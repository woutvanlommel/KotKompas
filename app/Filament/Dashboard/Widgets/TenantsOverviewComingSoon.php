<?php

namespace App\Filament\Dashboard\Widgets;

class TenantsOverviewComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 6;

    protected string $comingSoonHeading = 'Huurders overzicht';

    protected string $comingSoonDescription = 'Binnenkort zie je hier welke huurders in jouw koten zitten, per gebouw en per kot.';

    protected string $comingSoonIcon = 'heroicon-o-users';
}
