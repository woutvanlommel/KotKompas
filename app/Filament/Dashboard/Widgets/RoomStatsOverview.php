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
        return (auth()->user()?->hasRole('verhuurder') ?? false)
            && auth()->user()->hasRooms();
    }

    protected function getViewData(): array
    {
        // One grouped pass for the counts (total/available/rented) instead of three cloned queries.
        $countsByStatus = $this->roomsQuery()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $countsByStatus->sum();
        $available = (int) $countsByStatus->get('available', 0);
        $rented = (int) $countsByStatus->get('rented', 0);

        // Average keeps its own aggregate — it excludes archived, a different scope than the counts.
        $averagePrice = $this->roomsQuery()
            ->whereNot('status', 'archived')
            ->avg('price_per_month');

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
