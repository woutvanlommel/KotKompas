<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Computes and caches all scores from room_reviews.
 *
 * - Recency: reviews younger than 2 years count double.
 * - Room score = weighted average of room_scores (hygiene/size/value).
 * - Building score = weighted average across all reviews of its rooms.
 * - Landlord score = 50% room quality + 50% communication, across all their reviews.
 * - score_bayesian pulls the score toward the platform mean until enough
 *   reviews exist — only for ranking/filtering, never for display.
 */
class KotScoreService
{
    private const RECENT_YEARS = 2;

    private const RECENT_WEIGHT = 2.0;

    private const OLD_WEIGHT = 1.0;

    // "Weight" of the platform mean in the Bayesian score —
    // comparable to ~2.5 recent reviews.
    private const BAYES_CONFIDENCE = 5.0;

    // Starting value while the platform has no reviews at all.
    private const DEFAULT_PLATFORM_MEAN = 3.5;

    // Computed once per recompute operation; observers and the daily
    // cron reset this at the start so the value is never stale.
    private ?float $cachedPlatformMean = null;

    public function recomputeForReview(RoomReview $review): void
    {
        $this->recomputeFor($review->room_id, $review->landlord_id);
    }

    public function recomputeFor(int $roomId, int $landlordId): void
    {
        $this->cachedPlatformMean = null;

        if ($room = Room::with('building')->find($roomId)) {
            $this->recomputeRoom($room);
            $this->recomputeBuilding($room->building);
        }

        // withTrashed: reviews of a soft-deleted landlord may still
        // change; their cache must be correct when the account is restored.
        if ($landlord = User::withTrashed()->find($landlordId)) {
            $this->recomputeLandlord($landlord);
        }
    }

    public function recomputeAll(): void
    {
        $this->cachedPlatformMean = null;

        // The OR clause must stay grouped: lazyById appends its own
        // "id > ?" and a loose OR would bypass that cursor.
        Room::query()
            ->where(fn ($query) => $query->where('reviews_count', '>', 0)->orHas('reviews'))
            ->lazyById()
            ->each(fn (Room $room) => $this->recomputeRoom($room));

        // Iterate buildings separately (not via their rooms): this also heals
        // a building whose last reviewed room was deleted — the
        // DB cascade on room_reviews does not fire an observer.
        Building::query()
            ->where(fn ($query) => $query->where('reviews_count', '>', 0)->orHas('rooms.reviews'))
            ->lazyById()
            ->each(fn (Building $building) => $this->recomputeBuilding($building));

        User::withTrashed()
            ->where(fn ($query) => $query->where('landlord_reviews_count', '>', 0)->orHas('landlordReviews'))
            ->lazyById()
            ->each(fn (User $landlord) => $this->recomputeLandlord($landlord));
    }

    // The three entity recompute methods are private: only recomputeFor/recomputeAll
    // reset the memoized platform mean, so only they are a safe
    // veilig instappunt.
    private function recomputeRoom(Room $room): void
    {
        $reviews = $room->reviews()->get();
        [$score, $weight] = $this->weightedAverage($reviews, fn (RoomReview $r) => $r->room_score);

        $room->forceFill([
            'score' => $score,
            'score_bayesian' => $score === null ? null : $this->bayesian($score, $weight),
            'reviews_count' => $reviews->count(),
        ])->saveQuietly();
    }

    private function recomputeBuilding(Building $building): void
    {
        $reviews = RoomReview::query()
            ->whereIn('room_id', $building->rooms()->select('id'))
            ->get();

        [$score] = $this->weightedAverage($reviews, fn (RoomReview $r) => $r->room_score);

        $building->forceFill([
            'score' => $score,
            'reviews_count' => $reviews->count(),
        ])->saveQuietly();
    }

    private function recomputeLandlord(User $landlord): void
    {
        $reviews = $landlord->landlordReviews()->get();

        [$quality] = $this->weightedAverage($reviews, fn (RoomReview $r) => $r->room_score);
        [$communication] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_communication);

        $landlord->forceFill([
            'landlord_score' => $quality === null ? null : round(($quality + $communication) / 2, 2),
            'landlord_reviews_count' => $reviews->count(),
        ])->saveQuietly();
    }

    /**
     * Weighted averages per criterion, for the score section on the
     * detail page. Same recency weights as the room score itself, so
     * the breakdown and total never contradict each other.
     *
     * Communication is deliberately absent: it is asked in the survey and
     * counts toward the landlord score, but is never shown on room pages.
     *
     * @return array{hygiene: float, size: float, value: float}|null
     */
    public function criteriaBreakdown(Room $room): ?array
    {
        // Via the relationship property: an already loaded reviews relation
        // is reused instead of firing a second query.
        $reviews = $room->reviews;

        if ($reviews->isEmpty()) {
            return null;
        }

        [$hygiene] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_hygiene);
        [$size] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_size);
        [$value] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_value);

        return [
            'hygiene' => (float) $hygiene,
            'size' => (float) $size,
            'value' => (float) $value,
        ];
    }

    /**
     * @param  Collection<int, RoomReview>  $reviews
     * @param  callable(RoomReview): float  $value
     * @return array{0: float|null, 1: float} [weighted average, total weight]
     */
    private function weightedAverage(Collection $reviews, callable $value): array
    {
        if ($reviews->isEmpty()) {
            return [null, 0.0];
        }

        $cutoff = now()->subYears(self::RECENT_YEARS);
        $totalWeight = 0.0;
        $weightedSum = 0.0;

        foreach ($reviews as $review) {
            // created_at may be null for rows created outside Eloquent
            // (imports, bulk inserts) — those count as "old".
            $recent = $review->created_at !== null && $review->created_at->greaterThanOrEqualTo($cutoff);
            $weight = $recent ? self::RECENT_WEIGHT : self::OLD_WEIGHT;
            $totalWeight += $weight;
            $weightedSum += $weight * $value($review);
        }

        return [round($weightedSum / $totalWeight, 2), $totalWeight];
    }

    private function bayesian(float $score, float $weight): float
    {
        $platformMean = $this->cachedPlatformMean ??= $this->platformMean();

        return round(
            (self::BAYES_CONFIDENCE * $platformMean + $weight * $score) / (self::BAYES_CONFIDENCE + $weight),
            2,
        );
    }

    private function platformMean(): float
    {
        // Eén aggregatie in SQL — de tabel mag nooit gehydrateerd worden
        // for this average; this path runs during every submit request.
        $cutoff = now()->subYears(self::RECENT_YEARS);
        $weight = 'CASE WHEN created_at >= ? THEN ? ELSE ? END';

        $mean = RoomReview::query()
            ->selectRaw(
                "SUM(({$weight}) * (score_hygiene + score_size + score_value) / 3.0) / SUM({$weight}) AS mean",
                [$cutoff, self::RECENT_WEIGHT, self::OLD_WEIGHT, $cutoff, self::RECENT_WEIGHT, self::OLD_WEIGHT],
            )
            ->value('mean');

        return $mean === null ? self::DEFAULT_PLATFORM_MEAN : round((float) $mean, 2);
    }
}
