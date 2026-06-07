<?php

namespace App\Filament\Dashboard\Resources\Buildings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuildingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Beschrijving')
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
                    ->modalHeading('Gebouw bewerken')
                    ->form(self::getEditFormSchema()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEditFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Algemene informatie')
                    ->description('Basisgegevens van het pand')
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required(),
                        TextInput::make('description')
                            ->label('Beschrijving'),
                    ]),
                Step::make('Adres en locatie')
                    ->description('Adresgegevens en GPS-coördinaten')
                    ->schema([
                        TextInput::make('street')
                            ->label('Straat')
                            ->required(),
                        TextInput::make('house_number')
                            ->label('Huisnummer')
                            ->required()
                            ->numeric(),
                        TextInput::make('postal_code')
                            ->label('Postcode')
                            ->required()
                            ->numeric(),
                        TextInput::make('box')
                            ->label('Bus/Appartement'),
                        TextInput::make('city')
                            ->label('Plaats')
                            ->required(),
                        TextInput::make('country')
                            ->label('Land')
                            ->required(),
                        TextInput::make('longitude')
                            ->label('Lengtegraad')
                            ->numeric(),
                        TextInput::make('latitude')
                            ->label('Breedtegraad')
                            ->numeric(),
                    ]),
            ])
                ->columnSpan('full')
                ->skippable(),
        ];
    }
}
