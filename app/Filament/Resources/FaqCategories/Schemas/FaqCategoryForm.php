<?php

namespace App\Filament\Resources\FaqCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FaqCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Bilingual name stored as nested JSON: {"nl": "...", "en": "..."}.
                Tabs::make('Vertalingen')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Nederlands')->schema([
                            TextInput::make('name.nl')
                                ->label('Naam (NL)')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Tab::make('English')->schema([
                            TextInput::make('name.en')
                                ->label('Naam (EN)')
                                ->maxLength(255),
                        ]),
                    ]),
                TextInput::make('sort')
                    ->label('Volgorde')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Actief')
                    ->default(true),
            ]);
    }
}
