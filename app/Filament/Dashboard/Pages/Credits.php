<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\CreditPack;
use App\Models\CreditTransaction;
use App\Services\CreditService;
use App\Services\FilamentNotificationService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class Credits extends Page
{
    protected string $view = 'filament.dashboard.pages.credits';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $navigationLabel = 'Credits';

    protected static ?string $title = 'Credits';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('huurder') ?? false;
    }

    public function mount(): void
    {
        $status = request()->query('checkout');

        if ($status === 'success') {
            FilamentNotificationService::success(
                'Bedankt voor je aankoop!',
                'Je betaling wordt verwerkt — je credits staan zo dadelijk op je saldo.',
            );
        } elseif ($status === 'cancelled') {
            FilamentNotificationService::warning(
                'Betaling geannuleerd',
                'Er is niets in rekening gebracht.',
            );
        }

        // Query-param weghalen zodat een refresh de melding niet opnieuw toont.
        if ($status !== null) {
            $this->redirect(static::getUrl());
        }
    }

    // -------------------------------------------------------------------------
    // Data voor de view
    // -------------------------------------------------------------------------

    /** Huidig saldo = som van alle ledgerrijen (bron van waarheid). */
    public function getBalance(): int
    {
        return app(CreditService::class)->balance(auth()->user());
    }

    /** Actieve bundels, gesorteerd. */
    public function getPacks(): Collection
    {
        return CreditPack::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /** Laatste mutaties voor een compact overzicht. */
    public function getRecentTransactions(): Collection
    {
        return auth()->user()
            ->creditTransactions()
            ->latest()
            ->limit(5)
            ->get();
    }

    /** Leesbare omschrijving voor een ledgerrij. */
    public function transactionLabel(CreditTransaction $transaction): string
    {
        $reason = $transaction->reason;

        if (str_starts_with($reason, 'unlock_landlord:')) {
            return 'Verhuurder ontgrendeld';
        }

        return match ($reason) {
            'pack_purchase' => 'Credits gekocht',
            default => ucfirst(str_replace('_', ' ', $reason)),
        };
    }

    // -------------------------------------------------------------------------
    // Acties
    // -------------------------------------------------------------------------

    /** Bundel kopen -> Stripe Checkout (eenmalige betaling). */
    public function buy(int $packId)
    {
        $pack = CreditPack::where('is_active', true)->findOrFail($packId);
        $user = auth()->user();

        $checkout = $user->checkoutCharge($pack->price, $pack->name, 1, [
            'success_url' => static::getUrl().'?checkout=success',
            'cancel_url' => static::getUrl().'?checkout=cancelled',
            // Metadata draagt de webhook alles om idempotent credits bij te schrijven.
            'metadata' => [
                'type' => 'credit_purchase',
                'user_id' => (string) $user->id,
                'credit_pack_id' => (string) $pack->id,
                'credits' => (string) $pack->credits,
            ],
        ]);

        return redirect()->away($checkout->url);
    }
}
