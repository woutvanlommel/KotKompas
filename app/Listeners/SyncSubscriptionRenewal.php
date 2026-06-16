<?php

namespace App\Listeners;

use App\Services\FeaturedListingService;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * Houdt onze eigen kolommen op de subscriptions-tabel synchroon met Stripe:
 *  - renews_at: volgende verleng-/verloopdatum (zodat we die kunnen tonen zonder
 *    live Stripe te bevragen).
 *  - pending_stripe_price: leeggemaakt zodra de geplande wijziging echt is ingegaan.
 *
 * En houdt de uitgelichte koten ("uitgelicht") in lijn met het abonnement:
 *  - verlenging: schuift het featured-venster mee op naar de nieuwe periode;
 *  - downgrade: snoeit koten boven het nieuwe slot-aantal weg;
 *  - opzegging: haalt alle koten uit de uitgelicht-sectie.
 *
 * Luistert op WebhookHandled (ná Cashier's eigen verwerking).
 */
class SyncSubscriptionRenewal
{
    public function __construct(
        private readonly FeaturedListingService $featured,
    ) {}

    public function handle(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? null;

        if (! in_array($type, [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ], true)) {
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

        // Opgezegd -> meteen alle uitgelichte koten van deze verhuurder lossen.
        if ($type === 'customer.subscription.deleted') {
            if ($landlord = $subscription->owner) {
                $this->featured->unfeatureAll($landlord);
            }

            return;
        }

        // current_period_end: nieuwe Stripe ("basil") API zet 'm op het item,
        // oudere API top-level. Beide afvangen.
        $periodEnd = $data['items']['data'][0]['current_period_end']
            ?? ($data['current_period_end'] ?? null);

        $renewsAt = $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null;

        if ($renewsAt) {
            $subscription->renews_at = $renewsAt;
        }

        // Is de geplande wijziging ingegaan? (actieve prijs == pending) -> wachtrij leeg.
        $currentPrice = $data['items']['data'][0]['price']['id'] ?? null;

        if ($currentPrice && $subscription->pending_stripe_price === $currentPrice) {
            $subscription->pending_stripe_price = null;
        }

        $subscription->save();

        // Featured-venster meeschuiven naar de nieuwe periode en, bij een
        // downgrade, terugsnoeien tot het nieuwe slot-aantal.
        if ($landlord = $subscription->owner) {
            $this->featured->syncForLandlord(
                $landlord,
                $renewsAt ?? ($subscription->renews_at ? Carbon::parse($subscription->renews_at) : null),
            );
        }
    }
}
