<?php

namespace App\Listeners;

use Illuminate\Support\Carbon;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * Houdt onze eigen kolommen op de subscriptions-tabel synchroon met Stripe:
 *  - renews_at: volgende verleng-/verloopdatum (zodat we die kunnen tonen zonder
 *    live Stripe te bevragen).
 *  - pending_stripe_price: leeggemaakt zodra de geplande wijziging echt is ingegaan.
 *
 * Luistert op WebhookHandled (ná Cashier's eigen verwerking).
 */
class SyncSubscriptionRenewal
{
    public function handle(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? null;

        if (! in_array($type, ['customer.subscription.created', 'customer.subscription.updated'], true)) {
            return;
        }

        $data = $event->payload['data']['object'] ?? [];
        $stripeId = $data['id'] ?? null;

        if (! $stripeId) {
            return;
        }

        $subscription = Cashier::$subscriptionModel::where('stripe_id', $stripeId)->first();

        if (! $subscription) {
            return;
        }

        // current_period_end: nieuwe Stripe ("basil") API zet 'm op het item,
        // oudere API top-level. Beide afvangen.
        $periodEnd = $data['items']['data'][0]['current_period_end']
            ?? ($data['current_period_end'] ?? null);

        if ($periodEnd) {
            $subscription->renews_at = Carbon::createFromTimestamp($periodEnd);
        }

        // Is de geplande wijziging ingegaan? (actieve prijs == pending) -> wachtrij leeg.
        $currentPrice = $data['items']['data'][0]['price']['id'] ?? null;

        if ($currentPrice && $subscription->pending_stripe_price === $currentPrice) {
            $subscription->pending_stripe_price = null;
        }

        $subscription->save();
    }
}
