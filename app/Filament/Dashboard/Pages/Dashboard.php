<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Custom dashboard: a 12-column bento grid so widget WIDTH carries meaning
 * (the masthead full-bleed, the featured/subscription hero row asymmetric 7/5)
 * instead of an equal vertical card stack.
 */
class Dashboard extends BaseDashboard
{
    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 12,
        ];
    }
}
