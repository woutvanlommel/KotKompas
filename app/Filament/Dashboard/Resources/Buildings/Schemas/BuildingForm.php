<?php

namespace App\Filament\Dashboard\Resources\Buildings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard\Step;

class BuildingForm
{
    public static function getWizardSteps(): array
    {
        return [
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
                ]),
        ];
    }
}
