<?php

namespace App\Filament\Dashboard\Widgets;

class MessagesComingSoon extends ComingSoonWidget
{
    protected static ?int $sort = 6;

    /** @var string|array<string> */
    protected static string|array $requiredRole = ['huurder', 'verhuurder'];

    protected string $comingSoonHeading = 'Berichten';

    protected string $comingSoonDescription = 'Binnenkort zie je hier hoeveel ongelezen berichten je hebt en klik je rechtstreeks door naar je chat.';

    protected string $comingSoonIcon = 'heroicon-o-chat-bubble-left-right';
}
