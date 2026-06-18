<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CreditService;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Schrijft gekochte credits bij zodra Stripe een betaalde credit-aankoop bevestigt.
 *
 * Entitlements (hier: het saldo) worden ENKEL op basis van de geverifieerde
 * webhook toegekend, nooit op de success-redirect. Idempotent via de
 * stripe_session_id in CreditService::add().
 *
 * Luistert op WebhookReceived: Cashier verwerkt checkout.session.completed niet
 * zelf, maar dispatcht dit event wel voor élk binnenkomend (geverifieerd) event.
 */
class AddPurchasedCredits
{
    public function __construct(
        private readonly CreditService $credits,
    ) {}

    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'] ?? [];
        $metadata = $session['metadata'] ?? [];

        // Alleen onze credit-aankopen — abonnement-checkouts negeren.
        if (($metadata['type'] ?? null) !== 'credit_purchase') {
            return;
        }

        // Pas bijschrijven als de betaling daadwerkelijk rond is.
        if (($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $user = User::find($metadata['user_id'] ?? null);
        $credits = (int) ($metadata['credits'] ?? 0);
        $sessionId = $session['id'] ?? null;

        if (! $user || $credits <= 0 || ! $sessionId) {
            return;
        }

        // Idempotent: dubbele webhook met dezelfde session-ID schrijft niet dubbel bij.
        $this->credits->add($user, $credits, 'pack_purchase', $sessionId);
    }
}
