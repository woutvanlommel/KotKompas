<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Cashier\Subscription;

class SubscriptionSeeder extends Seeder
{
    /**
     * Koppelt elke verhuurder aan een plan via een (gesimuleerde) Cashier-
     * subscription. Gevarieerd zodat alle plan-states getest worden:
     *   - verhuurder@   → premium (3 featured slots) — basis voor highlight-test
     *   - sofie         → pro     (1 featured slot)
     *   - karel         → pro     (1 featured slot)
     *   - inge          → starter (0 featured slots)
     *   - bram          → geen actief abonnement
     *
     * currentPlan() matcht subscription.stripe_price aan
     * config('subscriptions.plans.{slug}') (= STRIPE_PRICE_* env). Staan die
     * env-vars niet ingevuld, dan kan het plan niet resolven — dat melden we.
     */
    public function run(): void
    {
        $assignments = [
            'verhuurder@kotkompas.be' => 'premium',
            'sofie.vermeulen@kotkompas.be' => 'pro',
            'karel.peeters@kotkompas.be' => 'pro',
            'inge.maes@kotkompas.be' => 'starter',
            // bram.janssens@kotkompas.be → bewust geen abonnement
        ];

        $missingPriceConfig = [];

        foreach ($assignments as $email => $slug) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->command->warn("Verhuurder {$email} niet gevonden — overgeslagen.");

                continue;
            }

            $priceId = config("subscriptions.plans.{$slug}");

            if (empty($priceId)) {
                $missingPriceConfig[] = $slug;
                // Placeholder die NIET per ongeluk matcht met een lege config,
                // zodat currentPlan() niet foutief 'Starter' teruggeeft.
                $priceId = "price_seed_missing_{$slug}";
            }

            // Cashier-customer id (vereist door Billable-helpers).
            if (empty($user->stripe_id)) {
                $user->forceFill(['stripe_id' => 'cus_seed_'.$user->id])->save();
            }

            Subscription::create([
                'user_id' => $user->id,
                'type' => 'default',
                'stripe_id' => 'sub_seed_'.$user->id,
                'stripe_status' => 'active',
                'stripe_price' => $priceId,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
                'renews_at' => now()->addMonth(),
            ]);

            $this->command->line("  ✓ {$email} → plan '{$slug}'");
        }

        if ($missingPriceConfig !== []) {
            $this->command->warn(
                'Let op: STRIPE_PRICE_* ontbreekt voor: '.implode(', ', array_unique($missingPriceConfig))
                .'. Vul deze in .env in zodat currentPlan() de plannen kan resolven.'
            );
        }
    }
}
