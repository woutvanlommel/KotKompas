<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

trait HasRoomInfoActions
{
    public function editBasicsAction(): Action
    {
        return Action::make('editBasics')
            ->label('Bewerken')
            ->slideOver()
            ->form([
                TextInput::make('title')
                    ->label('Kamertitel')
                    ->default(fn () => $this->record->title)
                    ->columnSpanFull(),
                TextInput::make('room_number')
                    ->label('Kamernummer')
                    ->required()
                    ->default(fn () => $this->record->room_number),
                TextInput::make('bus')
                    ->label('Bus')
                    ->placeholder('bv. 1, b, 3.01')
                    ->default(fn () => $this->record->bus),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'studio' => 'Studio',
                        'one_bedroom' => '1 slaapkamer',
                        'two_bedroom' => '2 slaapkamers',
                        'three_bedroom' => '3 slaapkamers',
                        'four_bedroom' => '4 slaapkamers',
                        'five_plus_bedroom' => '5+ slaapkamers',
                    ])
                    ->required()
                    ->default(fn () => $this->record->type),
                TextInput::make('price_per_month')
                    ->label('Prijs per maand')
                    ->numeric()
                    ->prefix('€')
                    ->required()
                    ->default(fn () => $this->record->price_per_month),
            ])
            ->action(function (array $data): void {
                $this->record->update([
                    'title' => $data['title'],
                    'room_number' => $data['room_number'],
                    'bus' => $data['bus'] ?? null,
                    'type' => $data['type'],
                    'price_per_month' => $data['price_per_month'],
                ]);
                $this->record->refresh();
            });
    }

    public function editKenmerkenAction(): Action
    {
        return Action::make('editKenmerken')
            ->label('Bewerken')
            ->slideOver()
            ->form([
                TextInput::make('surface_m2')
                    ->label('Oppervlakte')
                    ->numeric()
                    ->suffix('m²')
                    ->default(fn () => $this->record->surface_m2),
                Toggle::make('is_furnished')
                    ->label('Gemeubileerd')
                    ->default(fn () => $this->record->is_furnished),
                DatePicker::make('available_from')
                    ->label('Beschikbaar vanaf')
                    ->default(fn () => $this->record->available_from),
            ])
            ->action(function (array $data): void {
                $this->record->update([
                    'surface_m2' => $data['surface_m2'] ?? null,
                    'is_furnished' => $data['is_furnished'] ?? false,
                    'available_from' => $data['available_from'] ?? null,
                ]);
                $this->record->refresh();
            });
    }

    public function editDescriptionAction(): Action
    {
        return Action::make('editDescription')
            ->label('Bewerken')
            ->slideOver()
            ->form([
                RichEditor::make('description')
                    ->label('Beschrijving')
                    ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList'])
                    ->default(fn () => $this->record->description)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $this->record->update([
                    'description' => $data['description'] ?? null,
                ]);
                $this->record->refresh();
            });
    }
}
