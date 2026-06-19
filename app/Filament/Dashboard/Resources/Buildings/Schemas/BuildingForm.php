<?php

namespace App\Filament\Dashboard\Resources\Buildings\Schemas;

use App\Filament\Components\ImageUpload;
use App\Services\GeocodingService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
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
                                Select::make('address_lookup')
                                    ->label('Adres zoeken')
                                    ->placeholder('bv. Demerstraat 18, Hasselt')
                                    ->helperText('Typ een adres en kies een suggestie om de velden hieronder automatisch in te vullen.')
                                    ->searchable()
                                    ->searchPrompt('Begin te typen…')
                                    ->searchingMessage('Adressen zoeken…')
                                    ->noSearchResultsMessage('Geen adressen gevonden.')
                                    ->dehydrated(false)
                                    ->getSearchResultsUsing(function (string $search): array {
                                        $options = [];
                                        foreach (app(GeocodingService::class)->suggest($search) as $suggestion) {
                                            $key = base64_encode((string) json_encode($suggestion));
                                            $options[$key] = $suggestion['label'];
                                        }

                                        return $options;
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $data = json_decode(base64_decode((string) $value), true);

                                        return is_array($data) ? ($data['label'] ?? null) : null;
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $data = json_decode(base64_decode($state), true);

                                        if (! is_array($data)) {
                                            return;
                                        }

                                        $set('street', $data['street'] ?? '');
                                        $set('house_number', $data['house_number'] ?? '');
                                        $set('postal_code', $data['postal_code'] ?? '');
                                        $set('city', $data['city'] ?? '');

                                        // Coördinaten meteen meenemen uit de suggestie, zodat het
                                        // opslaan niet opnieuw hoeft te geocoden (= sneller).
                                        $set('latitude', $data['latitude'] ?? null);
                                        $set('longitude', $data['longitude'] ?? null);

                                        // Land enkel zetten als het in de keuzelijst zit (nu enkel BE).
                                        if (($data['country_code'] ?? null) === 'BE') {
                                            $set('country', 'BE');
                                        }
                                    })
                                    ->columnSpanFull(),
                                Hidden::make('latitude'),
                                Hidden::make('longitude'),
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
