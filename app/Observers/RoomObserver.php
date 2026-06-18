<?php

// Observer on Room: a rental ends when tenant_id changes away from a
// tenant — whether via unlinking or a swap ("Change tenant"). That is the
// moment for the review invitation, regardless of which action triggered it.
// The email that sends the link depends on "Template mail" (#28); until then
// the landlord shares the link via the dashboard (Status & Tenant).

namespace App\Observers;

use App\Models\ReviewInvitation;
use App\Models\Room;

class RoomObserver
{
    /**
     * A room that leaves "available" (rented, maintenance, archived) is no
     * longer publicly listed, so it must release its featured ("uitgelicht")
     * slot — otherwise a hidden room keeps consuming a subscription slot. Done
     * in `saving` so it persists in the same write, regardless of which action
     * changed the status (tenant link, table action, wizard, …).
     */
    public function saving(Room $room): void
    {
        if ($room->isDirty('status') && $room->status !== 'available' && $room->is_featured) {
            $room->is_featured = false;
            $room->featured_until = null;
        }
    }

    public function updated(Room $room): void
    {
        if (! $room->wasChanged('tenant_id')) {
            return;
        }

        $previousTenantId = $room->getOriginal('tenant_id');

        if ($previousTenantId === null) {
            return;
        }

        ReviewInvitation::issueFor($room, (int) $previousTenantId);
    }
}
