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
                    ->required(),
                TextInput::make('description'),
                TextInput::make('street')
                    ->required(),
                TextInput::make('house_number')
                    ->required()
                    ->numeric(),
                TextInput::make('postal_code')
                    ->required()
                    ->numeric(),
                TextInput::make('box'),
                TextInput::make('city')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('latitude')
                    ->numeric(),
            ]);
    }
}
