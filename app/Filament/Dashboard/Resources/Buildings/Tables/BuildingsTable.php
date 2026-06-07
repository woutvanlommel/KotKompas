<?php

namespace App\Filament\Dashboard\Resources\Buildings\Tables;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
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
            ->recordUrl(fn($record) => BuildingResource::getUrl('view', ['record' => $record]))
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
