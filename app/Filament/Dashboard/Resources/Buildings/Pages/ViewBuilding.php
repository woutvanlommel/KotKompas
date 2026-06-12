<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Rooms\Schemas\RoomWizard;
use App\Models\Building;
use App\Models\Room;
use App\Services\FilamentNotificationService;
use App\Services\GeocodingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
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
                    RoomWizard::make($this->record, [
                        Hidden::make('building_id')->default($this->record->id),
                    ]),
                ])
                ->action(function (array $data) {
                    // ── Kosten ──────────────────────────────────────────────
                    $costIds = $data['cost_types_data'] ?? [];
                    $pendingCostTypes = [];

                    foreach ($costIds as $id) {
                        $pendingCostTypes[$id] = [
                            'frequency' => $data["frequency_{$id}"] ?? 'monthly',
                            'amount' => $data["amount_{$id}"] ?? null,
                            'is_variable' => (bool) ($data["is_variable_{$id}"] ?? false),
                            'description' => $data["description_{$id}"] ?? null,
                        ];
                    }

                    // costs_included = true when there are monthly cost types
                    $data['costs_included'] = collect($pendingCostTypes)
                        ->contains(fn ($ct) => $ct['frequency'] === 'monthly');

                    // ── Faciliteiten ─────────────────────────────────────────
                    $facilityCatKeys = array_filter(array_keys($data), fn ($k) => str_starts_with($k, 'facility_cat_'));
                    $pendingFacilities = collect($data)
                        ->only($facilityCatKeys)
                        ->flatten()
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    // ── Verwijder wizard-specifieke keys vóór Room::create ───
                    $keysToRemove = array_merge(
                        ['cost_types_data'],
                        array_map(fn ($id) => "frequency_{$id}", $costIds),
                        array_map(fn ($id) => "amount_{$id}", $costIds),
                        array_map(fn ($id) => "is_variable_{$id}", $costIds),
                        array_map(fn ($id) => "description_{$id}", $costIds),
                        $facilityCatKeys,
                    );

                    foreach ($keysToRemove as $key) {
                        unset($data[$key]);
                    }

                    // ── Create room and sync pivots ───────────────────────────
                    $room = Room::create(array_merge($data, ['building_id' => $this->record->id]));

                    if (! empty($pendingCostTypes)) {
                        $room->costTypes()->sync($pendingCostTypes);
                    }

                    if (! empty($pendingFacilities)) {
                        $room->facilities()->sync($pendingFacilities);
                    }

                    FilamentNotificationService::success(
                        'Kamer toegevoegd',
                        'De kamer is succesvol toegevoegd.',
                        icon: 'heroicon-o-square-3-stack-3d'
                    );
                })
                ->successRedirectUrl(fn () => route('filament.dashboard.resources.buildings.view', $this->record)),
            Action::make('geocode')
                ->label('Locatie herberekenen')
                ->icon('heroicon-o-map-pin')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Locatie herberekenen')
                ->modalDescription('Dit herberekent de coördinaten van dit gebouw via het adres. Doorgaan?')
                ->modalSubmitActionLabel('Herbereken')
                ->action(function () {
                    $coordinates = app(GeocodingService::class)->geocodeBuilding($this->record);

                    if ($coordinates) {
                        $this->record->update([
                            'latitude' => $coordinates['latitude'],
                            'longitude' => $coordinates['longitude'],
                        ]);

                        FilamentNotificationService::success(
                            'Locatie bijgewerkt',
                            "Coördinaten van {$this->record->name} zijn bijgewerkt.",
                            icon: 'heroicon-o-map-pin'
                        );
                    } else {
                        FilamentNotificationService::danger(
                            'Locatie niet gevonden',
                            'Het adres kon niet worden geocodeerd. Controleer het adres.',
                            icon: 'heroicon-o-map-pin'
                        );
                    }
                }),
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
