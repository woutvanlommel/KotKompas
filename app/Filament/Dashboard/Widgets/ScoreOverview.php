<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Building;
use App\Support\Score;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Replaces the "Score overzicht" placeholder: the cached kotscores from
 * KotScoreService, seen through the lens of the logged-in landlord.
 *
 * Deliberately aggregates at BUILDING level only: a per-room score would
 * de-anonymise reviews — with one tenant per room, the landlord would know
 * exactly who wrote what.
 */
class ScoreOverview extends Widget
{
    protected string $view = 'filament.dashboard.widgets.score-overview';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 5];

    // Scores only change on a review submit and the nightly
    // recompute — the 5s polling default is wasted queries here.
    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
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
            'landlordScore' => $this->formatScore($landlord->landlord_score),
            'landlordReviewsCount' => $landlord->landlord_reviews_count,
            'landlordDescription' => $landlord->landlord_reviews_count > 0
                ? "Op basis van {$landlord->landlord_reviews_count} ".($landlord->landlord_reviews_count === 1 ? 'beoordeling' : 'beoordelingen').' — 50% kotkwaliteit, 50% communicatie'
                : 'Nog geen beoordelingen ontvangen',
            'averageScore' => $this->formatScore($averageScore !== null ? (float) $averageScore : null),
            'scoredCount' => $scoredCount,
            'averageDescription' => $scoredCount > 0
                ? "Over {$scoredCount} ".($scoredCount === 1 ? 'beoordeeld gebouw' : 'beoordeelde gebouwen')
                : 'Nog geen beoordeelde gebouwen',
            'bestBuildingScore' => $this->formatScore($bestBuilding?->score),
            'bestBuildingName' => $bestBuilding?->name ?? 'Nog geen beoordeelde gebouwen',
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
