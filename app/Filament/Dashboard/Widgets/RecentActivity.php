<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\RoomReview;
use App\Support\Score;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Recente activiteit — the dashboard's pulse: the latest reviews and new
 * rentals across the landlord's portfolio, merged into one reverse-chrono
 * feed. Mirrors Flux's "Team Activity" pattern, in the brand's voice.
 */
class RecentActivity extends Widget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.recent-activity';

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->hasRole('verhuurder') ?? false) && $user->hasRooms();
    }

    protected function getViewData(): array
    {
        $userId = auth()->id();

        $reviews = RoomReview::query()
            ->where('landlord_id', $userId)
            ->with('room.building:id,name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (RoomReview $r) => [
                'type' => 'review',
                'date' => $r->created_at,
                'title' => 'Nieuwe review',
                'subtitle' => $this->roomLabel($r->room),
                'meta' => '★ '.Score::format($r->room_score),
                'url' => $this->buildingUrl($r->room),
            ]);

        $rentals = RentalPeriod::query()
            ->whereHas('room.building', fn (Builder $q) => $q->where('landlord_id', $userId))
            ->with('room.building:id,name')
            ->latest('start_date')
            ->limit(8)
            ->get()
            ->map(fn (RentalPeriod $p) => [
                'type' => 'rental',
                'date' => $p->start_date,
                'title' => 'Nieuwe verhuur',
                'subtitle' => $this->roomLabel($p->room),
                'meta' => $p->start_date->format('d/m/Y'),
                'url' => $this->buildingUrl($p->room),
            ]);

        $items = $reviews->concat($rentals)
            ->filter(fn (array $i) => $i['date'] !== null)
            ->sortByDesc('date')
            ->take(8)
            ->values();

        return ['items' => $items];
    }

    private function roomLabel(?Room $room): string
    {
        if (! $room) {
            return 'Onbekend kot';
        }

        $name = $room->title ?: 'Kot '.$room->room_number;

        return $room->building ? $name.' · '.$room->building->name : $name;
    }

    private function buildingUrl(?Room $room): ?string
    {
        return $room?->building
            ? BuildingResource::getUrl('view', ['record' => $room->building])
            : null;
    }
}
