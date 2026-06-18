<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Subscription;
use App\Filament\Dashboard\Support\FeatureRoomToggle;
use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

/**
 * Dashboard card combining the landlord's subscription summary (plan, status,
 * renewal, manage link) with featured-slot usage and one-click "uitlichten"
 * toggles per room — one home for everything about featuring koten.
 */
class FeaturedRoomsManager extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.featured-rooms-manager';

    public static function canView(): bool
    {
        // Visible to every landlord — the subscription summary shows even before
        // they have rooms; the room list simply stays empty until then.
        return auth()->user()?->hasRole('verhuurder') ?? false;
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
            'remainingSlots' => $plan ? $user->remainingFeaturedSlots() : 0,
            'isSubscribed' => $plan !== null,
            'planLabel' => $plan?->label(),
            'renewsAt' => $plan ? $user->subscriptionRenewsAt() : null,
            'manageUrl' => Subscription::getUrl(),
        ];
    }
}
