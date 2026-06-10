<?php

namespace App\Filament\Dashboard\Widgets;

class TenantDocumentsComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 7;

    protected string $comingSoonHeading = 'Huurders met bestanden';

    protected string $comingSoonDescription = 'Binnenkort zie je hier per huurder welke documenten er aan het kot gekoppeld zijn.';

    protected string $comingSoonIcon = 'heroicon-o-document-duplicate';
}
