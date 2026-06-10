<?php

namespace App\Filament\Dashboard\Widgets;

class ScoreOverviewComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 4;

    protected string $comingSoonHeading = 'Score overzicht';

    protected string $comingSoonDescription = 'Binnenkort zie je hier de scores van je koten en gebouwen, en jouw totaalscore als verhuurder.';

    protected string $comingSoonIcon = 'heroicon-o-star';
}
