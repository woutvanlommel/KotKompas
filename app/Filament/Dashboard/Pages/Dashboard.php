<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Role-aware dashboard grid:
 * - Verhuurder → 12-column bento (masthead full-bleed, asymmetric hero row)
 * - Huurder    → default Filament stack (3 widgets, centered, readable)
 */
class Dashboard extends BaseDashboard
{
    public function getColumns(): array|int
    {
        if (auth()->user()?->hasRole('verhuurder')) {
            return [
                'default' => 1,
                'sm' => 2,
                'lg' => 12,
            ];
        }

        // Huurder: default Filament layout — single column, no bento
        return [
            'default' => 1,
        ];
    }
}
