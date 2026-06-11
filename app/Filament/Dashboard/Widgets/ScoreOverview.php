<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/**
 * Vervangt de "Score overzicht"-placeholder: de cached kotscores uit
 * KotScoreService, gezien door de bril van de ingelogde verhuurder.
 */
class ScoreOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Score overzicht';

    // Scores wijzigen alleen bij een review-submit en de nachtelijke
    // recompute — de 5s-polling-default is hier verspilde queries.
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

        $scoredRooms = $this->roomsQuery()->whereNotNull('score');

        $averageScore = (clone $scoredRooms)->avg('score');
        $scoredCount = (clone $scoredRooms)->count();
        $bestRoom = (clone $scoredRooms)
            ->orderByDesc('score')
            ->orderByDesc('reviews_count')
            ->first();

        return [
            Stat::make('Jouw verhuurderscore', $this->formatScore($landlord->landlord_score))
                ->description($landlord->landlord_reviews_count > 0
                    ? "Op basis van {$landlord->landlord_reviews_count} ".($landlord->landlord_reviews_count === 1 ? 'beoordeling' : 'beoordelingen').' — 50% kotkwaliteit, 50% communicatie'
                    : 'Nog geen beoordelingen ontvangen')
                ->color('info'),
            Stat::make('Gemiddelde kotscore', $this->formatScore($averageScore !== null ? (float) $averageScore : null))
                ->description($scoredCount > 0
                    ? "Over {$scoredCount} ".($scoredCount === 1 ? 'beoordeeld kot' : 'beoordeelde koten')
                    : 'Nog geen beoordeelde koten'),
            Stat::make('Best scorend kot', $this->formatScore($bestRoom?->score))
                ->description($bestRoom
                    ? ($bestRoom->title ?: 'Kamer '.$bestRoom->room_number)
                    : 'Nog geen beoordeelde koten'),
        ];
    }

    protected function roomsQuery(): Builder
    {
        return Room::query()->whereHas(
            'building',
            fn (Builder $query) => $query->where('landlord_id', auth()->id()),
        );
    }

    private function formatScore(?float $score): string
    {
        return $score === null ? '—' : number_format($score, 1, ',', '.').' / 5';
    }
}
