<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Status distribution as an editorial segmented bar (not a generic SaaS donut):
 * one hairline-gapped bar, each status a proportional segment, counts set as
 * figures in a micro-caps legend.
 */
class RoomStatusChart extends Widget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 7];

    protected string $view = 'filament.dashboard.widgets.room-status-bar';

    /** @var array<string, array{label: string, color: string}> */
    private const STATUSES = [
        'available' => ['label' => 'Beschikbaar', 'color' => '#15803d'],
        'rented' => ['label' => 'Verhuurd', 'color' => '#3a6ea5'],
        'maintenance' => ['label' => 'Onderhoud', 'color' => '#ff6700'],
        'archived' => ['label' => 'Gearchiveerd', 'color' => '#9aa6b4'],
    ];

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getViewData(): array
    {
        $counts = Room::query()
            ->whereHas('building', fn (Builder $query) => $query->where('landlord_id', auth()->id()))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $counts->sum();

        $segments = [];
        foreach (self::STATUSES as $status => $meta) {
            $count = (int) ($counts[$status] ?? 0);
            $segments[] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'count' => $count,
                'pct' => $total > 0 ? round($count / $total * 100, 2) : 0,
            ];
        }

        return ['segments' => $segments, 'total' => $total];
    }
}
