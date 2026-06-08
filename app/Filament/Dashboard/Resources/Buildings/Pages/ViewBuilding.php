<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\Building;
use App\Models\Room;
use App\Services\FilamentNotificationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard;
use Filament\Resources\Pages\ViewRecord;

/** @property Building $record */
class ViewBuilding extends ViewRecord
{
    protected static string $resource = BuildingResource::class;

    protected string $view = 'filament.dashboard.pages.buildings.view';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        abort_if($this->record->landlord_id !== auth()->id(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRoom')
                ->label('Kamer toevoegen')
                ->icon('heroicon-m-plus')
                ->slideOver()
                ->form([
                    Wizard::make([
                        Wizard\Step::make('Basis info')
                            ->description('Voer de basisigegevens in')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Kamertitel')
                                    ->required(),
                                TextInput::make('room_number')
                                    ->label('Kamernummer')
                                    ->required(),
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
                                    ->numeric(),
                                TextInput::make('surface_m2')
                                    ->label('Oppervlakte (m²)')
                                    ->required()
                                    ->numeric(),
                                Textarea::make('description')
                                    ->label('Beschrijving')
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
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'available' => 'Beschikbaar',
                                        'rented' => 'Verhuurd',
                                        'maintenance' => 'Onderhoud',
                                        'archived' => 'Gearchiveerd',
                                    ])
                                    ->required(),
                                Hidden::make('building_id')
                                    ->default($this->record->id),
                            ]),
                    ])
                        ->columnSpanFull()
                        ->nextAction(fn(Action $action) => $action->label('Volgende'))
                        ->previousAction(fn(Action $action) => $action->label('Vorige')),
                ])
                ->action(function (array $data) {
                    Room::create($data);
                    FilamentNotificationService::success(
                        'Kamer toegevoegd',
                        'De kamer is succesvol toegevoegd.',
                        icon: 'heroicon-o-square-3-stack-3d'
                    );
                })
                ->successRedirectUrl(fn () => route('filament.dashboard.resources.buildings.view', $this->record)),
            EditAction::make()
                ->label('Bewerken')
                ->slideOver()
                ->successNotification(null)
                ->after(function () {
                    FilamentNotificationService::success(
                        'Gebouw bijgewerkt',
                        "{$this->record->name} is bijgewerkt.",
                        icon: 'heroicon-o-building-office-2'
                    );
                }),
            DeleteAction::make()
                ->successNotification(null)
                ->after(function () {
                    FilamentNotificationService::success(
                        'Gebouw verwijderd',
                        "{$this->record->name} is verwijderd.",
                        icon: 'heroicon-o-building-office-2'
                    );
                }),
        ];
    }
}
