<?php

namespace App\Services;

use App\Models\LandlordUnlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LandlordUnlockService
{
    public function __construct(private CreditService $credits) {}

    /** Kost (in credits) om een verhuurder te unlocken. */
    public function cost(): int
    {
        return (int) config('credits.unlock_landlord_cost', 1);
    }

    /** Heeft de huurder al toegang tot deze verhuurder (betaald of via huurrelatie)? */
    public function isUnlocked(User $tenant, User $landlord): bool
    {
        return $tenant->canViewLandlord($landlord);
    }

    /**
     * Unlock de kaart van een verhuurder voor een huurder.
     * Schrijft credits af én maakt de entitlement-rij in één DB-transactie.
     * Idempotent: heeft de huurder al toegang (gekocht of via huurrelatie),
     * dan wordt er niets afgeschreven en geen nieuwe rij gemaakt.
     *
     * Gooit InsufficientCreditsException bij te weinig saldo.
     */
    public function unlock(User $tenant, User $landlord): ?LandlordUnlock
    {
        // Al toegang (betaald of afgeleid) → niets afschrijven.
        if ($tenant->canViewLandlord($landlord)) {
            return LandlordUnlock::where('tenant_id', $tenant->id)
                ->where('landlord_id', $landlord->id)
                ->first();
        }

        return DB::transaction(function () use ($tenant, $landlord) {
            // Dubbelcheck binnen de transactie (gelijktijdige requests).
            $existing = LandlordUnlock::where('tenant_id', $tenant->id)
                ->where('landlord_id', $landlord->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $cost = $this->cost();

            $transaction = $cost > 0
                ? $this->credits->spend($tenant, $cost, "unlock_landlord:{$landlord->id}")
                : null;

            // Een unique-violation (gelijktijdige unlock) gooit hier en rolt de hele
            // transactie terug, inclusief de credit-afschrijving — dus geen verlies.
            return LandlordUnlock::create([
                'tenant_id' => $tenant->id,
                'landlord_id' => $landlord->id,
                'credit_transaction_id' => $transaction?->id,
                'unlocked_at' => now(),
            ]);
        });
    }
}
