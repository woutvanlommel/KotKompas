<?php

namespace App\Filament\Dashboard\Resources\Buildings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BuildingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Naam')
                    ->required(),
                TextInput::make('description')
                    ->label('Beschrijving'),
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
            ]);
    }
}
