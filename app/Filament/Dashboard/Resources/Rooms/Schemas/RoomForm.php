<?php

namespace App\Filament\Dashboard\Resources\Rooms\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('building_id'),
                TextInput::make('room_number')
                    ->required(),
                Select::make('type')
                    ->options([
                        'studio' => 'Studio',
                        'one_bedroom' => 'One bedroom',
                        'two_bedroom' => 'Two bedroom',
                        'three_bedroom' => 'Three bedroom',
                        'four_bedroom' => 'Four bedroom',
                        'five_plus_bedroom' => 'Five plus bedroom',
                    ])
                    ->required(),
                TextInput::make('title'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price_per_month')
                    ->required()
                    ->numeric(),
                Toggle::make('costs_included')
                    ->required(),
                TextInput::make('extra_costs'),
                TextInput::make('surface_m2')
                    ->numeric(),
                Toggle::make('is_furnished')
                    ->required(),
                DatePicker::make('available_from'),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'rented' => 'Rented',
                        'maintenance' => 'Maintenance',
                        'archived' => 'Archived',
                    ])
                    ->required(),
            ]);
    }
}
