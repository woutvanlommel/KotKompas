<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\RoomReview;
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
        $user = auth()->user();

        return ($user?->hasRole('verhuurder') ?? false) && $user->hasRooms();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        // ONE grouped query: per-status count + summed rent, derive everything below.
        $byStatus = Room::query()
            ->whereHas('building', fn (Builder $q) => $q->where('landlord_id', $user->id))
            ->selectRaw('status, COUNT(*) as c, SUM(price_per_month) as rev')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $total = (int) $byStatus->sum('c');
        $rented = (int) ($byStatus->get('rented')->c ?? 0);
        $available = (int) ($byStatus->get('available')->c ?? 0);

        // Current omzet = rent of the rented koten; potential = that PLUS the rent
        // the currently-available koten would add if they were let too.
        $revenue = (int) ($byStatus->get('rented')->rev ?? 0);
        $availableRevenue = (int) ($byStatus->get('available')->rev ?? 0);
        $potential = $revenue + $availableRevenue;

        // Real month-over-month context (no invented percentages): koten that
        // started a rental this calendar month, and the rent they bring in.
        $monthStart = now()->startOfMonth();

        // Distinct koten that started a rental this month — a kot with several
        // periods this month must not inflate the count past the portfolio size.
        $newRentals = RentalPeriod::query()
            ->whereHas('room.building', fn (Builder $q) => $q->where('landlord_id', $user->id))
            ->where('start_date', '>=', $monthStart)
            ->with('room:id,price_per_month')
            ->get()
            ->unique('room_id');

        $newReviews = RoomReview::query()
            ->where('landlord_id', $user->id)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $hour = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 6 => 'Goeienacht',
            $hour < 12 => 'Goeiemorgen',
            $hour < 18 => 'Goeiemiddag',
            default => 'Goeienavond',
        };

        return [
            'greeting' => $greeting,
            'firstName' => $user->name,
            'occupancy' => $total > 0 ? (int) round($rented / $total * 100) : 0,
            'rented' => $rented,
            'total' => $total,
            'available' => $available,
            'revenue' => $revenue,
            'potential' => $potential,
            'yearly' => $revenue * 12,
            'newRentals' => $newRentals->count(),
            'newRevenue' => (int) $newRentals->sum(fn (RentalPeriod $p) => $p->room?->price_per_month ?? 0),
            'newReviews' => $newReviews,
            'manageUrl' => BuildingResource::getUrl(),
        ];
    }
}
