<?php

namespace App\Filament\Dashboard\Widgets;

class LandlordScoreComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 4;

    protected string $comingSoonHeading = 'Verhuurderscore';

    protected string $comingSoonDescription = 'Binnenkort zie je hier jouw score als verhuurder, berekend op basis van de scores van je koten en gebouwen.';

    protected string $comingSoonIcon = 'heroicon-o-star';
}
