<?php

namespace App\Filament\Dashboard\Resources\Rooms\Schemas;

use App\Models\Building;
use App\Models\CostType;
use App\Models\Facility;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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

                    // Bus only editable when the building has no bus number
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
                            'one_bedroom' => '1 slaapkamer',
                            'two_bedroom' => '2 slaapkamers',
                            'three_bedroom' => '3 slaapkamers',
                            'four_bedroom' => '4 slaapkamers',
                            'five_plus_bedroom' => '5+ slaapkamers',
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
                    DatePicker::make('available_from')
                        ->label('Beschikbaar vanaf')
                        ->required(),
                ]),

            Wizard\Step::make('Faciliteiten')
                ->description('Welke faciliteiten zijn aanwezig?')
                ->schema(
                    Facility::orderBy('category')->orderBy('name')
                        ->get()
                        ->groupBy('category')
                        ->map(function ($facilities, string $category) {
                            $key = 'facility_cat_'.preg_replace('/[^a-z0-9]+/', '_', strtolower($category));

                            return Section::make($category)
                                ->schema([
                                    CheckboxList::make($key)
                                        ->hiddenLabel()
                                        ->options($facilities->pluck('name', 'id')->toArray())
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->collapsed()
                                ->columnSpanFull();
                        })
                        ->values()
                        ->toArray()
                ),

            Wizard\Step::make('Kosten')
                ->description('Extra kosten bovenop de basishuur')
                ->schema([
                    Select::make('cost_types_data')
                        ->label('Kostensoorten')
                        ->multiple()
                        ->live()
                        ->searchable()
                        ->options(
                            CostType::orderBy('category')->orderBy('name')
                                ->get()
                                ->groupBy('category')
                                ->map(fn ($items) => $items->pluck('name', 'id'))
                                ->toArray()
                        )
                        ->columnSpanFull(),

                    Grid::make(1)
                        ->schema(fn (Get $get) => collect($get('cost_types_data') ?? [])
                            ->map(function (int|string $id) {
                                $costType = CostType::find($id);

                                if (! $costType) {
                                    return null;
                                }

                                return Fieldset::make($costType->name)
                                    ->schema([
                                        Select::make("frequency_{$id}")
                                            ->label('Frequentie')
                                            ->options([
                                                'monthly' => 'Maandelijks',
                                                'yearly' => 'Jaarlijks',
                                                'one_time' => 'Eenmalig',
                                            ])
                                            ->required()
                                            ->default('monthly'),
                                        TextInput::make("amount_{$id}")
                                            ->label('Bedrag')
                                            ->numeric()
                                            ->prefix('€')
                                            ->placeholder('Leeg laten indien variabel'),
                                        Toggle::make("is_variable_{$id}")
                                            ->label('Variabel (geen vaste prijs)')
                                            ->columnSpanFull(),
                                        TextInput::make("description_{$id}")
                                            ->label('Opmerking')
                                            ->maxLength(100)
                                            ->placeholder('Optioneel')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull();
                            })
                            ->filter()
                            ->values()
                            ->toArray()
                        )
                        ->columnSpanFull(),
                ]),
        ])
            ->columnSpanFull()
            ->nextAction(fn (Action $action) => $action->label('Volgende'))
            ->previousAction(fn (Action $action) => $action->label('Vorige'));
    }
}
