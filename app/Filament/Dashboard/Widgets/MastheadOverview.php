<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * The editorial masthead: the landlord's portfolio set as the front page of
 * their business — one monumental metric (occupancy) owning the first screen,
 * everything else a hairline-ruled footnote. Renders full-bleed (no card).
 */
class MastheadOverview extends Widget
{
    protected static ?int $sort = -100;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.masthead-overview';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        $rooms = fn (): Builder => Room::query()->whereHas(
            'building',
            fn (Builder $q) => $q->where('landlord_id', $user->id),
        );

        $total = $rooms()->count();
        $rented = $rooms()->where('status', 'rented')->count();
        $available = $rooms()->where('status', 'available')->count();
        $revenue = (int) $rooms()->where('status', 'rented')->sum('price_per_month');

        return [
            'occupancy' => $total > 0 ? (int) round($rented / $total * 100) : 0,
            'rented' => $rented,
            'total' => $total,
            'available' => $available,
            'revenue' => $revenue,
            'featured' => $user->featuredSlotsUsed(),
            'buildings' => $user->buildings()->count(),
            'score' => $user->landlord_score,
            'reviews' => (int) $user->landlord_reviews_count,
            'manageUrl' => BuildingResource::getUrl(),
        ];
    }
}
