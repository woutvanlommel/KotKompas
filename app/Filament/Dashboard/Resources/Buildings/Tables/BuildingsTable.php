<?php

namespace App\Filament\Dashboard\Resources\Buildings\Tables;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\Building;
use App\Services\FilamentNotificationService;
use App\Support\Score;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BuildingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => BuildingResource::getUrl('view', ['record' => $record]))
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn (Builder $q) => $q->where('status', 'available'),
                'rooms as rented_rooms_count' => fn (Builder $q) => $q->where('status', 'rented'),
            ]))
            ->columns([
                TextColumn::make('name')
                    ->label('Gebouw')
                    ->description(fn (Building $record): string => $record->full_address)
                    ->searchable(['name', 'street', 'city', 'postal_code'])
                    ->sortable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('rooms_count')
                    ->label('Kamers')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('bezetting')
                    ->label('Bezetting')
                    ->badge()
                    ->state(fn (Building $record): string => $record->rooms_count === 0
                        ? 'Geen kamers'
                        : $record->rented_rooms_count.'/'.$record->rooms_count.' verhuurd')
                    ->color(fn (Building $record): string => match (true) {
                        $record->rooms_count === 0 => 'gray',
                        $record->available_rooms_count > 0 => 'success',
                        default => 'info',
                    }),
                TextColumn::make('score')
                    ->label('Kotscore')
                    ->badge()
                    ->state(fn (Building $record): string => $record->reviews_count > 0 && $record->score !== null
                        ? Score::format($record->score).' ★'
                        : 'Nog geen score')
                    ->color(fn (Building $record): string => match (true) {
                        $record->reviews_count === 0 || $record->score === null => 'gray',
                        $record->score < 3.5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Aangemaakt op')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Bijgewerkt op')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color('gray')
                    ->successNotification(null)
                    ->after(function ($record) {
                        FilamentNotificationService::success(
                            'Gebouw bijgewerkt',
                            "{$record->name} is bijgewerkt.",
                            icon: 'heroicon-o-building-office-2'
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(null)
                        ->using(function ($records) {
                            $count = $records->count();
                            $firstName = $records->first()?->name;

                            $records->each->delete();

                            if ($count === 1) {
                                FilamentNotificationService::success(
                                    'Gebouw verwijderd',
                                    "{$firstName} is verwijderd.",
                                    icon: 'heroicon-o-building-office-2'
                                );
                            } else {
                                FilamentNotificationService::success(
                                    'Gebouwen verwijderd',
                                    "{$count} geselecteerde gebouwen zijn verwijderd.",
                                    icon: 'heroicon-o-building-office-2'
                                );
                            }
                        }),
                ]),
            ]);
    }
}
