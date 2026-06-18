<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No ->mutateFormDataUsing(['landlord_id' => ...]) here: a Room has no
            // landlord_id column. Ownership is derived from its parent Building
            // (building.landlord_id). The building_id is set on the form (Hidden,
            // seeded from the ?building_id= query param) and every read is scoped
            // via RoomResource::getEloquentQuery() whereHas('building', landlord_id).
            CreateAction::make()
                ->label('Kamer toevoegen')
                ->icon('heroicon-m-plus')
                ->slideOver(),
        ];
    }
}
