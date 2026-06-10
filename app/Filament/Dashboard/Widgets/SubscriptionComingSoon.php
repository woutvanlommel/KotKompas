<?php

namespace App\Filament\Dashboard\Widgets;

class SubscriptionComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 5;

    protected string $comingSoonHeading = 'Abonnement';

    protected string $comingSoonDescription = 'Binnenkort beheer je hier je abonnement en zie je in één oogopslag wat erin zit.';

    protected string $comingSoonIcon = 'heroicon-o-credit-card';
}
