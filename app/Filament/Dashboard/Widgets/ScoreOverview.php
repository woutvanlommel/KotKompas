<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Building;
use App\Support\Score;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/**
 * Replaces the "Score overzicht" placeholder: the cached kotscores from
 * KotScoreService, seen through the lens of the logged-in landlord.
 *
 * Deliberately aggregates at BUILDING level only: a per-room score would
 * de-anonymise reviews — with one tenant per room, the landlord would know
 * exactly who wrote what.
 */
class ScoreOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Score overzicht';

    // Scores only change on a review submit and the nightly
    // recompute — the 5s polling default is wasted queries here.
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

        $scoredBuildings = $this->buildingsQuery()->whereNotNull('score');

        $averageScore = (clone $scoredBuildings)->avg('score');
        $scoredCount = (clone $scoredBuildings)->count();
        $bestBuilding = (clone $scoredBuildings)
            ->orderByDesc('score')
            ->orderByDesc('reviews_count')
            ->first();

        return [
            Stat::make('Jouw verhuurderscore', $this->formatScore($landlord->landlord_score))
                ->description($landlord->landlord_reviews_count > 0
                    ? "Op basis van {$landlord->landlord_reviews_count} ".($landlord->landlord_reviews_count === 1 ? 'beoordeling' : 'beoordelingen').' — 50% kotkwaliteit, 50% communicatie'
                    : 'Nog geen beoordelingen ontvangen')
                ->color('info'),
            Stat::make('Gemiddelde gebouwscore', $this->formatScore($averageScore !== null ? (float) $averageScore : null))
                ->description($scoredCount > 0
                    ? "Over {$scoredCount} ".($scoredCount === 1 ? 'beoordeeld gebouw' : 'beoordeelde gebouwen')
                    : 'Nog geen beoordeelde gebouwen'),
            Stat::make('Best scorend gebouw', $this->formatScore($bestBuilding?->score))
                ->description($bestBuilding->name ?? 'Nog geen beoordeelde gebouwen'),
        ];
    }

    protected function buildingsQuery(): Builder
    {
        return Building::query()->where('landlord_id', auth()->id());
    }

    private function formatScore(?float $score): string
    {
        return $score === null ? '—' : Score::format($score).' / 5';
    }
}
