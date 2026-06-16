<?php

namespace App\Filament\Dashboard\Resources\Rooms\Tables;

use App\Filament\Dashboard\Support\FeatureRoomToggle;
use App\Models\Room;
use Filament\Actions\Action;
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
                IconColumn::make('featured')
                    ->label('Uitgelicht')
                    ->boolean()
                    ->getStateUsing(fn (Room $record): bool => $record->isFeatured()),
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
                Action::make('feature')
                    ->label(fn (Room $record): string => $record->isFeatured() ? 'Niet meer uitlichten' : 'Uitlichten')
                    ->icon(fn (Room $record): string => $record->isFeatured() ? 'heroicon-s-star' : 'heroicon-o-star')
                    ->color(fn (Room $record): string => $record->isFeatured() ? 'warning' : 'gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Room $record): string => $record->isFeatured() ? 'Kot niet meer uitlichten?' : 'Kot uitlichten?')
                    ->modalDescription(fn (Room $record): string => $record->isFeatured()
                        ? 'Het kot verdwijnt uit de uitgelichte sectie en zakt terug naar de normale volgorde.'
                        : 'Uitgelichte koten staan bovenaan de zoekresultaten. Dit gebruikt één uitlicht-slot van je abonnement.')
                    ->action(fn (Room $record) => FeatureRoomToggle::handle($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
