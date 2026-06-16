<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\KotScoreService;
use App\Support\Score;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Breaks the landlord's score open per criterion (hygiëne / grootte /
 * prijs-kwaliteit / communicatie) so a weak spot becomes actionable —
 * sits right after the ScoreOverview totals.
 *
 * KotScoreService pools the breakdown at portfolio level and returns null
 * below the anonymity threshold; this widget then shows an empty state
 * explaining how many reviews are still needed.
 */
class CriteriaBreakdown extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Score per criterium';

    // Scores only change on a review submit and the nightly recompute.
    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getStats(): array
    {
        $landlord = auth()->user();

        if (! $landlord) {
            return [];
        }

        $breakdown = app(KotScoreService::class)->landlordCriteriaBreakdown($landlord);

        if ($breakdown === null) {
            return [
                Stat::make('Score per criterium', '—')
                    ->description('Minstens '.KotScoreService::MIN_REVIEWS_FOR_BREAKDOWN.' beoordelingen nodig voor een anonieme opsplitsing'),
            ];
        }

        $labels = [
            'hygiene' => 'Hygiëne',
            'size' => 'Grootte',
            'value' => 'Prijs-kwaliteit',
            'communication' => 'Communicatie',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): Stat => Stat::make($label, Score::format($breakdown[$key]).' / 5'))
            ->values()
            ->all();
    }
}
