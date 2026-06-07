<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['landlord_id'] = auth()->id();
        return $data;
    }
}
