<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\KotScoreService;
use App\Support\Score;
use Filament\Widgets\Widget;

/**
 * The landlord's score in one place: the overall verhuurderscore (the
 * portfolio total — 50% kotkwaliteit, 50% communicatie) next to the exact
 * criteria it rolls up from (hygiëne / grootte / prijs-kwaliteit /
 * communicatie). Total + breakdown read as one story.
 *
 * The breakdown is pooled at portfolio level and hidden under the anonymity
 * threshold (MIN_REVIEWS_FOR_BREAKDOWN); below it the right column shows how
 * many reviews are still needed. Per-building scores live in the buildings
 * table, so they are intentionally not repeated here.
 */
class ScoreOverview extends Widget
{
    protected string $view = 'filament.dashboard.widgets.score-overview';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    // Scores only change on a review submit and the nightly recompute.
    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
    {
        $landlord = auth()->user();

        $minReviews = KotScoreService::MIN_REVIEWS_FOR_BREAKDOWN;

        if (! $landlord) {
            return [
                'landlordScore' => '—',
                'landlordDescription' => 'Nog geen beoordelingen ontvangen',
                'hasBreakdown' => false,
                'criteria' => [],
                'reviewsCount' => 0,
                'minReviews' => $minReviews,
            ];
        }

        $reviewsCount = $landlord->landlordReviews()->count();
        $breakdown = app(KotScoreService::class)->landlordCriteriaBreakdown($landlord);

        $labels = [
            'hygiene' => 'Hygiëne',
            'size' => 'Grootte',
            'value' => 'Prijs-kwaliteit',
            'communication' => 'Communicatie',
        ];

        $criteria = $breakdown === null ? [] : collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'score' => (float) $breakdown[$key],
            ])
            ->values()
            ->all();

        return [
            'landlordScore' => $landlord->landlord_score === null
                ? '—'
                : Score::format($landlord->landlord_score).' / 5',
            'landlordDescription' => $landlord->landlord_reviews_count > 0
                ? 'Op basis van '.$landlord->landlord_reviews_count.' '.($landlord->landlord_reviews_count === 1 ? 'beoordeling' : 'beoordelingen').' — 50% kotkwaliteit, 50% communicatie'
                : 'Nog geen beoordelingen ontvangen',
            'hasBreakdown' => $breakdown !== null,
            'criteria' => $criteria,
            'reviewsCount' => $reviewsCount,
            'minReviews' => $minReviews,
        ];
    }
}
