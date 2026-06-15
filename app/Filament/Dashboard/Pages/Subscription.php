<?php

namespace App\Filament\Dashboard\Pages;

use App\Enums\Plan as PlanEnum;
use App\Models\Plan;
use App\Services\FilamentNotificationService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends Page
{
    protected string $view = 'filament.dashboard.pages.subscription';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Abonnement';

    protected static ?string $title = 'Abonnement';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public function mount(): void
    {
        $status = request()->query('checkout');

        if ($status === 'success') {
            FilamentNotificationService::success(
                'Bedankt voor je betaling!',
                'Je abonnement wordt verwerkt en is zo dadelijk actief.',
            );
        } elseif ($status === 'cancelled') {
            FilamentNotificationService::warning(
                'Betaling geannuleerd',
                'Er is niets in rekening gebracht.',
            );
        }
    }

    public function getPlans(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function currentSubscription(): ?CashierSubscription
    {
        return auth()->user()->subscription('default');
    }

    public function isSubscribed(): bool
    {
        return auth()->user()->subscribed('default');
    }

    public function currentPriceId(): ?string
    {
        return $this->currentSubscription()?->stripe_price;
    }

    /** Nieuw abonnement -> Stripe Checkout (redirect). */
    public function subscribe(string $slug)
    {
        $plan = PlanEnum::tryFrom($slug);
        abort_unless($plan !== null, 404);

        return auth()->user()
            ->newSubscription('default', $plan->priceId())
            ->checkout([
                'success_url' => static::getUrl() . '?checkout=success',
                'cancel_url' => static::getUrl() . '?checkout=cancelled',
            ])
            ->redirect();
    }

    /** Wisselen van plan (gebruikt bestaande betaalmethode, geen Checkout). */
    public function swap(string $slug): void
    {
        $plan = PlanEnum::tryFrom($slug);
        abort_unless($plan !== null && $this->isSubscribed(), 404);

        auth()->user()->subscription('default')->swap($plan->priceId());

        FilamentNotificationService::success('Je plan is gewijzigd.');
    }

    /** Opzeggen aan einde van de periode (grace period). */
    public function cancel(): void
    {
        $this->currentSubscription()?->cancel();

        FilamentNotificationService::success(
            'Abonnement opgezegd',
            'Je behoudt toegang tot het einde van de huidige periode.',
        );
    }

    /** Opzegging ongedaan maken zolang in grace period. */
    public function resume(): void
    {
        $this->currentSubscription()?->resume();

        FilamentNotificationService::success('Abonnement hervat.');
    }
}
