<?php

namespace App\Filament\Dashboard\Resources\Rooms\Schemas;

use App\Models\Building;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;

class RoomWizard
{
    public static function make(?Building $building = null, array $extraLastStepFields = []): Wizard
    {
        return Wizard::make([
            Wizard\Step::make('Basis info')
                ->description('Voer de basisgegevens in')
                ->schema([
                    TextInput::make('title')
                        ->label('Kamertitel')
                        ->required(),
                    TextInput::make('room_number')
                        ->label('Kamernummer')
                        ->required(),

                    // Adres van het gebouw (read-only)
                    TextInput::make('_street')
                        ->label('Straat')
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component) => $component->state($building?->street)),
                    TextInput::make('_house_number')
                        ->label('Huisnummer')
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component) => $component->state($building?->house_number)),
                    TextInput::make('_postal_code')
                        ->label('Postcode')
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component) => $component->state($building?->postal_code)),
                    TextInput::make('_city')
                        ->label('Plaats')
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component) => $component->state($building?->city)),

                    // Bus enkel invulbaar als het gebouw geen bus heeft
                    TextInput::make('bus')
                        ->label('Bus')
                        ->placeholder('bv. 1, b, 3.01')
                        ->visible($building !== null && $building->bus === null),
                ]),

            Wizard\Step::make('Details')
                ->description('Voeg details toe')
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'studio' => 'Studio',
                            'one_bedroom' => 'One bedroom',
                            'two_bedroom' => 'Two bedroom',
                            'three_bedroom' => 'Three bedroom',
                            'four_bedroom' => 'Four bedroom',
                            'five_plus_bedroom' => 'Five plus bedroom',
                        ])
                        ->required(),
                    TextInput::make('price_per_month')
                        ->label('Prijs per maand')
                        ->required()
                        ->numeric()
                        ->prefix('€'),
                    TextInput::make('surface_m2')
                        ->label('Oppervlakte')
                        ->required()
                        ->numeric()
                        ->suffix('m²'),
                    RichEditor::make('description')
                        ->label('Beschrijving')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),
                ]),

            Wizard\Step::make('Aanvullend')
                ->description('Vul aanvullende info in')
                ->schema([
                    Toggle::make('is_furnished')
                        ->label('Gemeubileerd'),
                    Toggle::make('costs_included')
                        ->label('Kosten inbegrepen'),
                    DatePicker::make('available_from')
                        ->label('Beschikbaar vanaf')
                        ->required(),
                    // Select::make('status')
                    //     ->label('Status')
                    //     ->options([
                    //         'available' => 'Beschikbaar',
                    //         'rented' => 'Verhuurd',
                    //         'maintenance' => 'Onderhoud',
                    //         'archived' => 'Gearchiveerd',
                    //     ])
                    //     ->required()
                    //     ->live(),
                    // Select::make('tenant_id')
                    //     ->label('Huurder')
                    //     ->placeholder('Zoek op naam of e-mail…')
                    //     ->searchable()
                    //     ->getSearchResultsUsing(
                    //         fn (string $search): array => User::role('huurder')
                    //             ->where(fn ($q) => $q
                    //                 ->where('name', 'like', "%{$search}%")
                    //                 ->orWhere('email', 'like', "%{$search}%")
                    //             )
                    //             ->limit(20)
                    //             ->get()
                    //             ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                    //             ->all()
                    //     )
                    //     ->getOptionLabelUsing(
                    //         fn ($value): ?string => User::find($value)?->name
                    //     )
                    //     ->nullable()
                    //     ->visible(fn (Get $get): bool => $get('status') === 'rented'),
                    // ...$extraLastStepFields,
                ]),
        ])
            ->columnSpanFull()
            ->nextAction(fn (Action $action) => $action->label('Volgende'))
            ->previousAction(fn (Action $action) => $action->label('Vorige'));
    }
}
