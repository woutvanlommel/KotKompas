<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Dot-notation writes into the single `content` json column
                // (model casts content => array): {"vraag": "...", "antwoord": "..."}.
                TextInput::make('content.vraag')
                    ->label('Vraag')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('content.antwoord')
                    ->label('Antwoord')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
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
