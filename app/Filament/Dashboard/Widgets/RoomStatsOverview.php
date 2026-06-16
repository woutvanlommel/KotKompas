<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class RoomStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 5];

    protected ?string $heading = 'Koten overzicht';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getStats(): array
    {
        $rooms = $this->roomsQuery();

        $total = (clone $rooms)->count();
        $available = (clone $rooms)->where('status', 'available')->count();
        $rented = (clone $rooms)->where('status', 'rented')->count();
        $averagePrice = (clone $rooms)->whereNot('status', 'archived')->avg('price_per_month');

        return [
            Stat::make('Koten verhuurd', "{$rented} van {$total}")
                ->description("{$available} nog beschikbaar")
                ->color('info'),
            Stat::make('Gem. basishuur', $averagePrice !== null ? Number::currency((float) $averagePrice, 'EUR', 'nl_BE') : '—')
                ->description('Per maand, excl. vaste kosten'),
        ];
    }

    protected function roomsQuery(): Builder
    {
        return Room::query()->whereHas(
            'building',
            fn (Builder $query) => $query->where('landlord_id', auth()->id()),
        );
    }
}
