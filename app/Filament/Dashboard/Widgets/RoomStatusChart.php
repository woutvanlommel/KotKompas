<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Room;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class RoomStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Statusverdeling';

    protected ?string $description = 'Jouw koten per status';

    protected ?string $maxHeight = '260px';

    protected const STATUSES = [
        'available' => ['label' => 'Beschikbaar', 'color' => '#004e98'],
        'rented' => ['label' => 'Verhuurd', 'color' => '#3a6ea5'],
        'maintenance' => ['label' => 'Onderhoud', 'color' => '#ff6700'],
        'archived' => ['label' => 'Gearchiveerd', 'color' => '#c0c0c0'],
    ];

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Room::query()
            ->whereHas('building', fn (Builder $query) => $query->where('landlord_id', auth()->id()))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'labels' => array_column(self::STATUSES, 'label'),
            'datasets' => [
                [
                    'data' => array_map(fn (string $status) => $counts[$status] ?? 0, array_keys(self::STATUSES)),
                    'backgroundColor' => array_column(self::STATUSES, 'color'),
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
