<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\Building;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BuildingsOverviewTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gebouwen overzicht')
            ->description('Bezetting en huurprijs per gebouw')
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
                    ),
            )
            ->recordUrl(fn ($record) => BuildingResource::getUrl('view', ['record' => $record]))
            ->columns([
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
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
