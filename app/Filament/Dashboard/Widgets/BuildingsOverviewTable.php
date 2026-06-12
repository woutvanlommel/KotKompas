<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Building;
use Filament\Actions\Action;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BuildingsOverviewTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /** @var array<int> */
    public array $expandedBuildings = [];

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public function toggleBuilding(int $id): void
    {
        if (in_array($id, $this->expandedBuildings)) {
            $this->expandedBuildings = array_values(
                array_filter($this->expandedBuildings, fn ($bid) => $bid !== $id)
            );
        } else {
            $this->expandedBuildings[] = $id;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gebouwen overzicht')
            ->description('Bezetting, huurprijs en kotscore per kamer')
            ->query(
                Building::query()
                    ->where('landlord_id', auth()->id())
                    ->withCount([
                        'rooms',
                        'rooms as available_rooms_count' => fn (Builder $query) => $query->where('status', 'available'),
                        'rooms as rented_rooms_count' => fn (Builder $query) => $query->where('status', 'rented'),
                    ])
                    ->withAvg(
                        ['rooms as average_price' => fn (Builder $query) => $query->whereNot('status', 'archived')],
                        'price_per_month',
                    )
                    ->with([
                        'rooms' => fn ($query) => $query
                            ->with('tenant')
                            ->orderBy('room_number'),
                    ]),
            )
            ->recordAction('toggleBuilding')
            ->recordActions([
                Action::make('toggleBuilding')
                    ->action(fn (Building $record) => $this->toggleBuilding($record->id))
                    ->icon(fn (Building $record) => in_array($record->id, $this->expandedBuildings)
                        ? 'heroicon-o-chevron-up'
                        : 'heroicon-o-chevron-down')
                    ->label('')
                    ->color('gray'),
            ])
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->label('Gebouw')
                        ->sortable(),
                    TextColumn::make('city')
                        ->label('Plaats')
                        ->sortable(),
                    TextColumn::make('rented_rooms_count')
                        ->label('Koten verhuurd')
                        ->state(fn ($record) => $record->rooms_count > 0
                            ? "{$record->rented_rooms_count} van {$record->rooms_count}"
                            : null)
                        ->placeholder('Geen koten')
                        ->badge()
                        ->color(fn ($record) => $record->available_rooms_count > 0 ? 'success' : 'gray')
                        ->sortable(),
                    TextColumn::make('average_price')
                        ->label('Gem. basishuur')
                        ->money('EUR', locale: 'nl_BE')
                        ->placeholder('—')
                        ->sortable(),
                ]),
                Panel::make([
                    View::make('filament.dashboard.widgets.building-rooms'),
                ])->visible(fn (Building $record) => in_array($record->id, $this->expandedBuildings)),
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
