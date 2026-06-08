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
                // Category is managed inline here — create or edit one without
                // leaving the FAQ form (no separate resource needed).
                Select::make('faq_category_id')
                    ->label('Categorie')
                    ->relationship('category')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->naam)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm(self::categoryFields())
                    ->editOptionForm(self::categoryFields())
                    ->columnSpanFull(),

                // FAQ content: nested JSON {"vraag": {nl, en}, "antwoord": {nl, en}}.
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

    /**
     * Category fields, reused by the inline create + edit option forms.
     */
    protected static function categoryFields(): array
    {
        return [
            Tabs::make('Vertalingen')
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
        ];
    }
}
