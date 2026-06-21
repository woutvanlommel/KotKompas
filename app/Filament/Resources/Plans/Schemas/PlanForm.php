<?php

namespace App\Filament\Resources\Plans\Schemas;

use App\Enums\Plan as PlanEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slug')
                    ->label('Plan')
                    ->options(collect(PlanEnum::cases())->mapWithKeys(
                        fn (PlanEnum $p) => [$p->value => $p->label()]
                    ))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Bepaalt de Stripe-prijs (via config). Eén plan per slug.'),

                TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Beschrijving')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('monthly_price')
                    ->label('Prijs per maand')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('€')
                    ->helperText('Enkel ter weergave — werk dezelfde prijs ook bij in Stripe, anders wijkt het af van wat afgerekend wordt.'),

                TagsInput::make('features')
                    ->label('Features')
                    ->placeholder('Voeg een feature toe')
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Volgorde')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Actief')
                    ->columnSpanFull()
                    ->default(true),
            ]);
    }
}
