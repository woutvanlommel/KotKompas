<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Featuring ("uitlichten") a room: gates on the landlord's plan slots and
 * mirrors the subscription period into Room::featured_until, so the public
 * result page can sort featured-first with a single-table query. The
 * WebhookHandled listener reuses syncForLandlord()/unfeatureAll() to keep the
 * paid window and slot count in step with Stripe.
 */
class FeaturedListingService
{
    /**
     * Feature a room for its landlord. Returns false (and changes nothing)
     * when the landlord has no plan or no free slots left.
     */
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

        $room->is_featured = true;
        $room->featured_until = $this->windowEnd($landlord);
        $room->save();

        return true;
    }

    /** Stop featuring a room (always allowed). */
    public function unfeature(Room $room): void
    {
        $room->is_featured = false;
        $room->featured_until = null;
        $room->save();
    }

    /**
     * Reconcile a landlord's featured rooms with their subscription after a
     * Stripe webhook: extend the paid window to the new period, and trim any
     * rooms beyond the (possibly downgraded) slot count.
     */
    public function syncForLandlord(User $landlord, ?Carbon $periodEnd): void
    {
        // No valid plan / no slots left -> drop every featured room.
        if ($landlord->featuredSlots() < 1) {
            $this->unfeatureAll($landlord);

            return;
        }

        // Bump the window by intent (is_featured), not by the active scope, so
        // rooms whose window just lapsed at the renewal moment are re-extended
        // instead of silently dropping off.
        if ($periodEnd !== null) {
            $this->intendedRooms($landlord)->update(['featured_until' => $periodEnd]);
        }

        $this->pruneToSlots($landlord);
    }

    /** Drop all of a landlord's featured rooms (e.g. on cancellation). */
    public function unfeatureAll(User $landlord): void
    {
        $this->intendedRooms($landlord)->update([
            'is_featured' => false,
            'featured_until' => null,
        ]);
    }

    /**
     * Trim featured rooms beyond the landlord's current slot count, dropping
     * the lowest-scored first so the best rooms keep their spot.
     */
    private function pruneToSlots(User $landlord): void
    {
        $slots = $landlord->featuredSlots();

        $rooms = $this->intendedRooms($landlord)
            ->orderByRaw('coalesce(score_bayesian, -1) desc')
            ->get();

        if ($rooms->count() <= $slots) {
            return;
        }

        $rooms->slice($slots)->each(fn (Room $room) => $this->unfeature($room));
    }

    /**
     * Rooms a landlord intends to feature (flag set), regardless of whether
     * their paid window is still open.
     *
     * @return Builder<Room>
     */
    private function intendedRooms(User $landlord): Builder
    {
        return Room::query()
            ->where('is_featured', true)
            ->whereHas('building', fn ($query) => $query->where('landlord_id', $landlord->id));
    }

    /**
     * How long the featured window runs: the landlord's current period end.
     * Only reached for a valid subscription (slots > 0), so when the renewal
     * date can't be resolved we fall back to a month out — the renewal webhook
     * corrects it on the next event.
     */
    private function windowEnd(User $landlord): Carbon
    {
        return $landlord->subscriptionRenewsAt() ?? now()->addMonth();
    }
}
