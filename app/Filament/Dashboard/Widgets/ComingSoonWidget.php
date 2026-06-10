<?php

namespace App\Filament\Dashboard\Widgets;

use Filament\Widgets\Widget;

abstract class ComingSoonWidget extends Widget
{
    protected string $view = 'filament.dashboard.widgets.coming-soon';

    /** @var string|array<string> */
    protected static string|array $requiredRole = 'verhuurder';

    protected string $comingSoonHeading;

    protected string $comingSoonDescription;

    protected string $comingSoonIcon;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(static::$requiredRole) ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'heading' => $this->comingSoonHeading,
            'description' => $this->comingSoonDescription,
            'icon' => $this->comingSoonIcon,
        ];
    }
}
