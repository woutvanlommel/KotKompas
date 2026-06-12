<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Building;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class BuildingsOverviewTable extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.buildings-overview';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public function getBuildings(): Collection
    {
        return Building::query()
            ->where('landlord_id', auth()->id())
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn (Builder $query) => $query->where('status', 'available'),
                'rooms as rented_rooms_count'    => fn (Builder $query) => $query->where('status', 'rented'),
            ])
            ->withAvg(
                ['rooms as average_price' => fn (Builder $query) => $query->whereNot('status', 'archived')],
                'price_per_month',
            )
            ->with([
                'rooms' => fn ($query) => $query->with('tenant')->orderBy('room_number'),
            ])
            ->orderBy('name')
            ->get();
    }
}
