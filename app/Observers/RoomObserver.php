<?php

// Observer op Room: een huur is stopgezet zodra tenant_id wijzigt weg van een
// huurder — bij ontkoppelen én bij een wissel ("Huurder wijzigen"). Dat is hét
// moment voor de enquête-uitnodiging, ongeacht via welke actie het gebeurde.
// De mail die de link verstuurt hangt aan "Template mail" (#28); tot die er is
// deelt de verhuurder de link via het dashboard (Status & Huurder).

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
