<?php

namespace App\Filament\Dashboard\Widgets;

use Filament\Widgets\Widget;

abstract class ComingSoonWidget extends Widget
{
    protected string $view = 'filament.dashboard.widgets.coming-soon';

    protected string $comingSoonHeading;

    protected string $comingSoonDescription;

    protected string $comingSoonIcon;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
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
