<?php

namespace App\Filament\Dashboard\Resources\Buildings\Schemas;

use App\Filament\Components\ImageUpload;
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
                        Step::make('Foto\'s')
                            ->completedIcon(Heroicon::HandThumbUp)
                            ->columnSpanFull()
                            ->schema([
                                ImageUpload::make('images')
                                    ->label('Foto\'s van het gebouw'),
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
                                    ->placeholder('bv. 15 of 15A')
                                    ->required(),
                                TextInput::make('bus')
                                    ->label('Bus')
                                    ->placeholder('bv. 1, b, 3.01'),
                                TextInput::make('postal_code')
                                    ->label('Postcode')
                                    ->required()
                                    ->numeric(),
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
