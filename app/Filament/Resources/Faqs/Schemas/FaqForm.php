<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // JSON name lives in a nested column, so resolve the label manually.
                Select::make('faq_category_id')
                    ->label('Categorie')
                    ->relationship('category')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->naam)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                // Nested JSON in the single `content` column:
                // {"vraag": {"nl": "...", "en": "..."}, "antwoord": {"nl": "...", "en": "..."}}.
                Tabs::make('Vertalingen')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Nederlands')
                            ->schema([
                                TextInput::make('content.vraag.nl')
                                    ->label('Vraag (NL)')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('content.antwoord.nl')
                                    ->label('Antwoord (NL)')
                                    ->required()
                                    ->rows(5),
                            ]),
                        Tab::make('English')
                            ->schema([
                                TextInput::make('content.vraag.en')
                                    ->label('Vraag (EN)')
                                    ->maxLength(255),
                                Textarea::make('content.antwoord.en')
                                    ->label('Antwoord (EN)')
                                    ->rows(5),
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
