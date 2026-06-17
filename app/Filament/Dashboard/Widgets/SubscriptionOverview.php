<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Subscription;
use Filament\Widgets\Widget;

/**
 * Dashboard card: which plan the landlord is on, how many of their featured
 * ("uitlicht") slots are in use, and a link straight to the Abonnement tab.
 */
class SubscriptionOverview extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 5];

    protected string $view = 'filament.dashboard.widgets.subscription-overview';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $plan = $user->currentPlan();

        return [
            'isSubscribed' => $plan !== null,
            'planLabel' => $plan?->label(),
            'renewsAt' => $plan ? $user->subscriptionRenewsAt() : null,
            'manageUrl' => Subscription::getUrl(),
        ];
    }
}
