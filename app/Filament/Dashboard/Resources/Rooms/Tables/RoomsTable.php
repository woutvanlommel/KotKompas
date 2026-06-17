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
                TextColumn::make('building.name')
                    ->label('Gebouw')
                    ->sortable(),
                TextColumn::make('room_number')
                    ->label('Kamernummer')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable(),
                TextColumn::make('price_per_month')
                    ->label('Huurprijs/maand')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('costs_included')
                    ->label('Kosten inbegrepen')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->costTypes->where('pivot.frequency', 'monthly')->isEmpty()),
                TextColumn::make('surface_m2')
                    ->label('Oppervlakte (m²)')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_furnished')
                    ->label('Gemeubileerd')
                    ->boolean(),
                TextColumn::make('available_from')
                    ->label('Beschikbaar vanaf')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
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
                    ->color(fn (Room $record): string => $record->isFeatured() ? 'featured' : 'gray')
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
