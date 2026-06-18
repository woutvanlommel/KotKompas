<?php

namespace App\Filament\Dashboard\Support;

use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use App\Services\FilamentNotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Shared handler behind every "Huurder koppelen / wijzigen" control (rooms
 * table + room view): closes any running rental period, opens a fresh one with
 * the new primary tenant and flips the room to "verhuurd". One home so the
 * table action and the ViewRoom header action can never drift apart.
 */
class LinkRoomTenant
{
    public static function handle(Room $room, int $tenantId, bool $notify = false): void
    {
        $newTenant = User::findOrFail($tenantId);

        // Only actual huurders may be linked as a tenant — the Select only
        // surfaces them, but the submitted id is client-controlled, so the
        // role gate is enforced server-side here (covers table + ViewRoom).
        abort_unless($newTenant->hasRole('huurder'), 403);

        DB::transaction(function () use ($room, $newTenant) {
            // Sluit lopende periode(s) af
            $room->rentalPeriods()
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->each(fn (RentalPeriod $rp) => $rp->update([
                    'end_date' => now()->subDay()->toDateString(),
                ]));

            // Nieuwe periode aanmaken — datums worden ingevuld via het contract
            $period = RentalPeriod::create([
                'room_id' => $room->id,
                'start_date' => now()->toDateString(),
                'end_date' => null,
            ]);

            $period->tenants()->attach($newTenant->id, ['is_primary' => true]);

            // tenant_id op room bewaren voor RoomObserver (review invitations)
            $room->update([
                'tenant_id' => $newTenant->id,
                'status' => 'rented',
            ]);
        });

        $room->refresh();

        if ($notify) {
            FilamentNotificationService::success(
                'Huurder gekoppeld',
                "{$newTenant->full_name} is gekoppeld aan dit kot — de status staat nu op verhuurd.",
            );
        }
    }
}
