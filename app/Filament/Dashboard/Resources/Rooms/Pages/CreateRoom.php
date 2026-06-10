<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use App\Models\Room;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    protected static string $resource = RoomResource::class;

    protected array $pendingCostTypes = [];

    protected array $pendingFacilities = [];

    public function mount(): void
    {
        parent::mount();

        if (request()->has('building_id')) {
            $this->form->fill([
                'building_id' => request()->query('building_id'),
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Kosten
        $costIds = $data['cost_types_data'] ?? [];

        foreach ($costIds as $id) {
            $this->pendingCostTypes[$id] = [
                'frequency' => $data["frequency_{$id}"] ?? 'monthly',
                'amount' => $data["amount_{$id}"] ?? null,
                'is_variable' => (bool) ($data["is_variable_{$id}"] ?? false),
                'description' => $data["description_{$id}"] ?? null,
            ];
        }

        // costs_included = true als er maandelijkse kostensoorten zijn
        $data['costs_included'] = collect($this->pendingCostTypes)
            ->contains(fn ($ct) => ($ct['frequency'] ?? '') === 'monthly');

        $keysToRemove = array_merge(
            ['cost_types_data'],
            array_map(fn ($id) => "frequency_{$id}", $costIds),
            array_map(fn ($id) => "amount_{$id}", $costIds),
            array_map(fn ($id) => "is_variable_{$id}", $costIds),
            array_map(fn ($id) => "description_{$id}", $costIds),
        );

        // Faciliteiten — checkbox per categorie (facility_cat_* keys)
        $facilityCatKeys = array_filter(array_keys($data), fn ($k) => str_starts_with($k, 'facility_cat_'));
        $facilityIds = collect($data)
            ->only($facilityCatKeys)
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->pendingFacilities = $facilityIds;

        $keysToRemove = array_merge($keysToRemove, $facilityCatKeys);

        foreach ($keysToRemove as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Room $room */
        $room = $this->record;

        if (! empty($this->pendingCostTypes)) {
            $room->costTypes()->sync($this->pendingCostTypes);
        }

        if (! empty($this->pendingFacilities)) {
            $room->facilities()->sync($this->pendingFacilities);
        }
    }
}
