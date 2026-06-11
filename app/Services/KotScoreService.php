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

    // "Gewicht" van het platformgemiddelde in de Bayesiaanse score —
    // vergelijkbaar met ~2,5 recente beoordelingen.
    private const BAYES_CONFIDENCE = 5.0;

    // Startwaarde zolang het platform nog geen enkele beoordeling heeft.
    private const DEFAULT_PLATFORM_MEAN = 3.5;

    // Eén keer berekend per recompute-operatie; observers en de dagelijkse
    // command resetten dit aan het begin zodat de waarde nooit stale is.
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

        // withTrashed: reviews van een soft-deleted verhuurder kunnen nog
        // wijzigen; zijn cache moet kloppen als de account ge-restored wordt.
        if ($landlord = User::withTrashed()->find($landlordId)) {
            $this->recomputeLandlord($landlord);
        }
    }

    public function recomputeAll(): void
    {
        $this->cachedPlatformMean = null;

        // De or-voorwaarde moet gegroepeerd blijven: lazyById voegt zelf
        // "id > ?" toe en een losse OR zou die cursor omzeilen.
        Room::query()
            ->where(fn ($query) => $query->where('reviews_count', '>', 0)->orHas('reviews'))
            ->lazyById()
            ->each(fn (Room $room) => $this->recomputeRoom($room));

        // Gebouwen apart itereren (niet via hun koten): zo geneest ook een
        // gebouw waarvan het laatste beoordeelde kot verwijderd is — de
        // DB-cascade op room_reviews vuurt geen observer af.
        Building::query()
            ->where(fn ($query) => $query->where('reviews_count', '>', 0)->orHas('rooms.reviews'))
            ->lazyById()
            ->each(fn (Building $building) => $this->recomputeBuilding($building));

        User::withTrashed()
            ->where(fn ($query) => $query->where('landlord_reviews_count', '>', 0)->orHas('landlordReviews'))
            ->lazyById()
            ->each(fn (User $landlord) => $this->recomputeLandlord($landlord));
    }

    // De drie entiteit-recomputes zijn privé: alleen recomputeFor/recomputeAll
    // resetten het gememoizede platformgemiddelde, dus alleen zij zijn een
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
     * @return array{hygiene: float, size: float, value: float, communication: float}|null
     */
    public function criteriaBreakdown(Room $room): ?array
    {
        // Via de relatie-property: een al geladen reviews-relatie wordt
        // hergebruikt i.p.v. een tweede query af te vuren.
        $reviews = $room->reviews;

        if ($reviews->isEmpty()) {
            return null;
        }

        [$hygiene] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_hygiene);
        [$size] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_size);
        [$value] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_value);
        [$communication] = $this->weightedAverage($reviews, fn (RoomReview $r) => (float) $r->score_communication);

        return [
            'hygiene' => (float) $hygiene,
            'size' => (float) $size,
            'value' => (float) $value,
            'communication' => (float) $communication,
        ];
    }

    /**
     * @param  Collection<int, RoomReview>  $reviews
     * @param  callable(RoomReview): float  $value
     * @return array{0: float|null, 1: float} [gewogen gemiddelde, totaalgewicht]
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
            // created_at kan null zijn bij rijen die buiten Eloquent om zijn
            // aangemaakt (imports, bulk inserts) — die tellen als "oud".
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
        // voor dit gemiddelde, dit pad loopt in de request van elke submit.
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
