<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Subscription;
use App\Filament\Dashboard\Support\FeatureRoomToggle;
use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

/**
 * Dashboard card to feature ("uitlichten") rooms in one click, without digging
 * into a building. Lists the landlord's available rooms with a star toggle and
 * shows how many featured slots are in use.
 */
class FeaturedRoomsManager extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 7];

    protected string $view = 'filament.dashboard.widgets.featured-rooms-manager';

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->hasRole('verhuurder') ?? false) && $user->hasRooms();
    }

    /** Toggle a room's featured state (scoped to the landlord's own rooms). */
    public function toggle(int $room): void
    {
        FeatureRoomToggle::handle(
            auth()->user()->rooms()->findOrFail($room)
        );
    }

    /** @return Collection<int, Room> */
    public function getRooms(): Collection
    {
        return auth()->user()->rooms()
            ->where('rooms.status', 'available')
            ->with('building:id,name')
            ->orderBy('buildings.name')
            ->orderByDesc('is_featured')
            ->orderBy('room_number')
            ->get();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $plan = $user->currentPlan();

        return [
            // Grouped per building (name-ordered), featured rooms first within each.
            'groups' => $this->getRooms()->groupBy(fn (Room $room) => $room->building->name),
            'slotsUsed' => $user->featuredSlotsUsed(),
            'slotsTotal' => $plan ? (int) config("subscriptions.featured_slots.{$plan->value}", 0) : 0,
            'manageUrl' => Subscription::getUrl(),
        ];
    }
}
