<?php

namespace App\Filament\Dashboard\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('room_number')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('price_per_month')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('costs_included')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->costTypes->where('pivot.frequency', 'monthly')->isEmpty()),
                TextColumn::make('surface_m2')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_furnished')
                    ->boolean(),
                TextColumn::make('available_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
