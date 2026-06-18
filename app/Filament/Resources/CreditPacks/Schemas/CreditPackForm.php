<?php

namespace App\Filament\Resources\CreditPacks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CreditPackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Weergavenaam van de bundel, bv. "100 credits".'),

                TextInput::make('credits')
                    ->label('Aantal credits')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('price')
                    ->label('Prijs')
                    ->prefix('€')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    // DB bewaart cents; formulier toont/leest euro's.
                    ->formatStateUsing(fn (?int $state) => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),

                TextInput::make('sort_order')
                    ->label('Volgorde')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Actief')
                    ->columnSpanFull()
                    ->default(true),

                Toggle::make('is_featured')
                    ->label('Aanbevolen')
                    ->helperText('Geeft deze bundel het "Aanbevolen"-accent op de kooppagina.')
                    ->columnSpanFull()
                    ->default(false),
            ]);
    }
}
