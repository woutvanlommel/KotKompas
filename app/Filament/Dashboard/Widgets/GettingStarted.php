<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Widgets\Widget;

/**
 * Onboarding for a brand-new landlord (no koten yet): one guided welcome block
 * instead of scattered empty data widgets. Mutually exclusive with the data
 * widgets — they gate on hasRooms() being true, this gates on it being false.
 */
class GettingStarted extends Widget
{
    protected string $view = 'filament.dashboard.widgets.getting-started';

    protected static ?int $sort = -200;

    protected int|string|array $columnSpan = 'full';

    // Static onboarding copy — nothing to poll.
    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->hasRole('verhuurder') ?? false) && ! $user->hasRooms();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        // The first building (oldest) is where step 2 sends them to add rooms.
        // Rooms are created from the building view via the RoomsRelationManager,
        // so the next-action target is that building's view page — null until
        // step 1 is done.
        $firstBuilding = $user->buildings()->oldest('id')->first();

        return [
            'hasBuildings' => $firstBuilding !== null,
            // No dedicated create route exists; the "Nieuw gebouw" slide-over
            // lives on the buildings index, so that is the next-action target.
            'createBuildingUrl' => BuildingResource::getUrl('index'),
            // Step 2: rooms are added from the building view (RoomsRelationManager).
            'addRoomsUrl' => $firstBuilding
                ? BuildingResource::getUrl('view', ['record' => $firstBuilding])
                : null,
        ];
    }
}
