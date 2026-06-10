<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TenantRoomInfo extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.tenant-room-info';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('huurder') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'rooms' => $this->rooms(),
        ];
    }

    protected function rooms(): Collection
    {
        return Room::query()
            ->where('tenant_id', auth()->id())
            ->with(['building', 'costTypes', 'media'])
            ->get();
    }
}
