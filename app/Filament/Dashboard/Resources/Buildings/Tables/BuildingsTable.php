<?php

namespace App\Filament\Dashboard\Resources\Buildings\Tables;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Services\FilamentNotificationService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuildingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => BuildingResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                TextColumn::make('street')
                    ->label('Straat')
                    ->searchable(),
                TextColumn::make('house_number')
                    ->label('Huisnummer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('postal_code')
                    ->label('Postcode')
                    ->sortable(),
                TextColumn::make('box')
                    ->label('Bus/Appartement')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Plaats')
                    ->searchable(),
                TextColumn::make('country')
                    ->label('Land')
                    ->searchable(),
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
