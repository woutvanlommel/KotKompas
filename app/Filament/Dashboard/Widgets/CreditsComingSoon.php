<?php

namespace App\Filament\Dashboard\Widgets;

class CreditsComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 3;

    /** @var string|array<string> */
    protected static string|array $requiredRole = 'huurder';

    protected string $comingSoonHeading = 'Credits';

    protected string $comingSoonDescription = 'Binnenkort koop en beheer je hier je credits, en zie je precies wat je ermee kan doen.';

    protected string $comingSoonIcon = 'heroicon-o-banknotes';
}
