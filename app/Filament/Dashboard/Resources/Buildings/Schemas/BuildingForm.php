<?php

namespace App\Filament\Dashboard\Resources\Buildings\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BuildingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->columnSpanFull()
                    ->schema([
                        Step::make('Basis gegevens')
                            ->completedIcon(Heroicon::HandThumbUp)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Naam')
                                    ->required(),
                                RichEditor::make('description')
                                    ->label('Beschrijving')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'undo',
                                        'redo',
                                    ]),
                            ]),
                        Step::make('Adres')
                            ->completedIcon(Heroicon::HandThumbUp)
                            ->columnSpanFull()
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
                                Select::make('country')
                                    ->label('Land')
                                    ->options([
                                        'BE' => 'België',
                                        // 'Nederland' => 'Nederland',
                                    ])
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
