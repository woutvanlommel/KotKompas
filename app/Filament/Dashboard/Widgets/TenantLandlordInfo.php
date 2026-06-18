<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Chat;
use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TenantLandlordInfo extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.tenant-landlord-info';

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->hasRole('huurder') ?? false)
            && Room::query()->where('tenant_id', $user->id)->exists();
    }

    protected function getViewData(): array
    {
        return [
            'landlords' => $this->landlords(),
            'chatUrl' => Chat::getUrl(),
        ];
    }

    protected function landlords(): Collection
    {
        return Room::query()
            ->where('tenant_id', auth()->id())
            ->with('building.landlord')
            ->get()
            ->groupBy(fn (Room $room) => $room->building->landlord_id)
            ->map(fn (Collection $rooms) => [
                'landlord' => $rooms->first()->building->landlord,
                'rooms' => $rooms,
            ])
            ->values();
    }
}
