<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class Profile extends Page
{
    protected string $view = 'filament.dashboard.pages.profile';

    protected static bool $shouldRegisterNavigation = false;
}
