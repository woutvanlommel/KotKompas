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
