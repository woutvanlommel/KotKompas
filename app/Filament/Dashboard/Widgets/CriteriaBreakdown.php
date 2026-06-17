<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\KotScoreService;
use Filament\Widgets\Widget;

/**
 * Breaks the landlord's score open per criterion (hygiëne / grootte /
 * prijs-kwaliteit / communicatie) so a weak spot becomes actionable —
 * sits right after the ScoreOverview totals.
 *
 * KotScoreService pools the breakdown at portfolio level and returns null
 * below the anonymity threshold; this widget then shows an empty state
 * explaining how many reviews are still needed.
 */
class CriteriaBreakdown extends Widget
{
    protected string $view = 'filament.dashboard.widgets.criteria-breakdown';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 7];

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
                'hasBreakdown' => false,
                'criteria' => [],
                'reviewsCount' => 0,
                'minReviews' => $minReviews,
            ];
        }

        $reviewsCount = $landlord->landlordReviews()->count();

        $breakdown = app(KotScoreService::class)->landlordCriteriaBreakdown($landlord);

        if ($breakdown === null) {
            return [
                'hasBreakdown' => false,
                'criteria' => [],
                'reviewsCount' => $reviewsCount,
                'minReviews' => $minReviews,
            ];
        }

        $labels = [
            'hygiene' => 'Hygiëne',
            'size' => 'Grootte',
            'value' => 'Prijs-kwaliteit',
            'communication' => 'Communicatie',
        ];

        $criteria = collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'score' => (float) $breakdown[$key],
            ])
            ->values()
            ->all();

        return [
            'hasBreakdown' => true,
            'criteria' => $criteria,
            'reviewsCount' => $reviewsCount,
            'minReviews' => $minReviews,
        ];
    }
}
