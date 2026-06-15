<?php

namespace App\Filament\Dashboard\Pages;

use App\Enums\Plan as PlanEnum;
use App\Models\Plan;
use App\Services\FilamentNotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Cashier\Cashier;
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

        // Query-param weghalen zodat een refresh de melding niet opnieuw toont.
        if ($status !== null) {
            $this->redirect(static::getUrl());
        }
    }

    // -------------------------------------------------------------------------
    // Data voor de view
    // -------------------------------------------------------------------------

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

    /** Het plan dat in de wachtrij staat (uitgestelde wijziging), of null. */
    public function pendingPlan(): ?Plan
    {
        $priceId = $this->currentSubscription()?->pending_stripe_price;

        if (! $priceId) {
            return null;
        }

        foreach (PlanEnum::cases() as $case) {
            if ($case->priceId() === $priceId) {
                return $case->model();
            }
        }

        return null;
    }

    /** Verleng-/verloopdatum: uit onze DB (gesynct via webhook), met live Stripe als fallback. */
    public function getRenewalDate(): ?Carbon
    {
        $subscription = $this->currentSubscription();

        if (! $subscription) {
            return null;
        }

        if ($subscription->renews_at) {
            return Carbon::parse($subscription->renews_at);
        }

        try {
            $stripeSub = $subscription->asStripeSubscription();
            $ts = $stripeSub->items->data[0]->current_period_end
                ?? ($stripeSub->current_period_end ?? null);

            return $ts ? Carbon::createFromTimestamp($ts) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Acties (Filament-modals)
    // -------------------------------------------------------------------------

    /** Nieuw abonnement -> Stripe Checkout (redirect). Geen modal nodig. */
    public function subscribe(string $slug)
    {
        $plan = PlanEnum::tryFrom($slug);
        abort_unless($plan !== null, 404);

        $checkout = auth()->user()
            ->newSubscription('default', $plan->priceId())
            ->checkout([
                'success_url' => static::getUrl().'?checkout=success',
                'cancel_url' => static::getUrl().'?checkout=cancelled',
            ]);

        return redirect()->away($checkout->url);
    }

    /** Wisselen van plan -> gaat in bij volgende verlenging (Stripe subscription schedule). */
    public function swapAction(): Action
    {
        return Action::make('swap')
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-arrows-right-left')
            ->modalIconColor('primary')
            ->modalHeading('Abonnement wijzigen')
            ->modalDescription(function (array $arguments): string {
                $plan = Plan::where('slug', $arguments['slug'] ?? '')->first();
                $when = $this->renewalLabel() ?? 'je volgende verlenging';

                return "Je wijzigt naar {$plan?->name}. Dit gaat in op {$when}. "
                    .'Tot dan blijf je gewoon op je huidige plan.';
            })
            ->modalSubmitActionLabel('Bevestig wijziging')
            ->action(fn (array $arguments) => $this->performSwap($arguments['slug'] ?? ''));
    }

    /** Opzeggen -> loopt af op einde periode (grace period). */
    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->requiresConfirmation()
            ->color('danger')
            ->modalIcon('heroicon-o-x-circle')
            ->modalIconColor('danger')
            ->modalHeading('Abonnement opzeggen')
            ->modalDescription(function (): string {
                $when = $this->renewalLabel() ?? 'het einde van je huidige periode';

                return "Je abonnement loopt dan af op {$when}. Tot dan behoud je volledige toegang.";
            })
            ->modalSubmitActionLabel('Ja, opzeggen')
            ->action(function (): void {
                $this->currentSubscription()?->cancel();

                FilamentNotificationService::success(
                    'Abonnement opgezegd',
                    'Je behoudt toegang tot het einde van de huidige periode.',
                );
            });
    }

    /** Opzegging ongedaan maken zolang in grace period. */
    public function resumeAction(): Action
    {
        return Action::make('resume')
            ->requiresConfirmation()
            ->color('success')
            ->modalIcon('heroicon-o-arrow-path')
            ->modalHeading('Abonnement hervatten')
            ->modalDescription('Je abonnement loopt dan gewoon door zoals voorheen.')
            ->modalSubmitActionLabel('Hervatten')
            ->action(function (): void {
                $this->currentSubscription()?->resume();

                FilamentNotificationService::success('Abonnement hervat.');
            });
    }

    /** Een geplande (nog niet ingegane) wijziging annuleren. */
    public function cancelPendingSwapAction(): Action
    {
        return Action::make('cancelPendingSwap')
            ->requiresConfirmation()
            ->color('gray')
            ->modalIcon('heroicon-o-x-mark')
            ->modalHeading('Geplande wijziging annuleren')
            ->modalDescription('Je blijft dan gewoon op je huidige plan, er verandert niets.')
            ->modalSubmitActionLabel('Wijziging annuleren')
            ->action(function (): void {
                $subscription = $this->currentSubscription();

                if (! $subscription) {
                    return;
                }

                try {
                    $stripeSub = $subscription->asStripeSubscription();

                    if ($stripeSub->schedule) {
                        Cashier::stripe()->subscriptionSchedules->release($stripeSub->schedule);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }

                $subscription->pending_stripe_price = null;
                $subscription->save();

                FilamentNotificationService::success('Geplande wijziging geannuleerd.');
            });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Datum + "over x dagen" in het Nederlands, of null. */
    protected function renewalLabel(): ?string
    {
        $date = $this->getRenewalDate()?->locale('nl');

        if (! $date) {
            return null;
        }

        return $date->isoFormat('D MMMM YYYY').' ('.$date->diffForHumans().')';
    }

    /** Maakt een Stripe subscription schedule die bij periode-einde naar het nieuwe plan overgaat. */
    protected function performSwap(string $slug): void
    {
        $plan = PlanEnum::tryFrom($slug);
        $subscription = $this->currentSubscription();

        if ($plan === null || $subscription === null) {
            return;
        }

        $newPriceId = $plan->priceId();

        if ($subscription->stripe_price === $newPriceId) {
            return; // al op dit plan
        }

        try {
            $stripe = Cashier::stripe();
            $stripeSub = $subscription->asStripeSubscription();

            // Bestaande schedule hergebruiken, anders een nieuwe maken vanaf het abonnement.
            if ($stripeSub->schedule) {
                $schedule = $stripe->subscriptionSchedules->retrieve($stripeSub->schedule);
            } else {
                $schedule = $stripe->subscriptionSchedules->create([
                    'from_subscription' => $stripeSub->id,
                ]);
            }

            $currentPhase = $schedule->phases[0];

            // Fase 1: huidige prijs tot periode-einde. Fase 2: nieuwe prijs daarna.
            $stripe->subscriptionSchedules->update($schedule->id, [
                'end_behavior' => 'release',
                'phases' => [
                    [
                        'items' => [['price' => $subscription->stripe_price, 'quantity' => 1]],
                        'start_date' => $currentPhase->start_date,
                        'end_date' => $currentPhase->end_date,
                    ],
                    [
                        'items' => [['price' => $newPriceId, 'quantity' => 1]],
                    ],
                ],
            ]);

            // Lokaal bewaren voor weergave (DB = bron van waarheid).
            $subscription->pending_stripe_price = $newPriceId;
            $subscription->renews_at = Carbon::createFromTimestamp($currentPhase->end_date);
            $subscription->save();

            FilamentNotificationService::success(
                'Wijziging gepland',
                'Je nieuwe plan gaat in bij je volgende verlenging.',
            );
        } catch (\Throwable $e) {
            report($e);

            FilamentNotificationService::danger(
                'Wijzigen mislukt',
                'Er ging iets mis bij het plannen van de wijziging. Probeer het later opnieuw.',
            );
        }
    }
}
