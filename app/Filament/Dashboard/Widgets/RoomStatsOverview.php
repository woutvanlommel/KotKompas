<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class RoomStatsOverview extends Widget
{
    protected string $view = 'filament.dashboard.widgets.room-stats-overview';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 5];

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
    {
        $rooms = $this->roomsQuery();

        $total = (clone $rooms)->count();
        $available = (clone $rooms)->where('status', 'available')->count();
        $rented = (clone $rooms)->where('status', 'rented')->count();
        $averagePrice = (clone $rooms)->whereNot('status', 'archived')->avg('price_per_month');

        return [
            'total' => $total,
            'available' => $available,
            'rented' => $rented,
            'averagePrice' => $averagePrice !== null ? Number::currency((float) $averagePrice, 'EUR', 'nl_BE') : null,
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
