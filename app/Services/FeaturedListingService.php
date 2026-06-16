<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;


class FeaturedListingService
{
    
    public function feature(Room $room): bool
    {
        $landlord = $room->building?->landlord;

        if ($landlord === null) {
            return false;
        }

        if ($room->isFeatured()) {
            return true;
        }

        if ($landlord->remainingFeaturedSlots() < 1) {
            return false;
        }

        $room->featured_until = $this->windowEnd($landlord);
        $room->save();

        return true;
    }

    /** Stop featuring a room (always allowed). */
    public function unfeature(Room $room): void
    {
        $room->featured_until = null;
        $room->save();
    }


    private function windowEnd(User $landlord): Carbon
    {
        return $landlord->subscriptionRenewsAt() ?? now()->addMonth();
    }
}
