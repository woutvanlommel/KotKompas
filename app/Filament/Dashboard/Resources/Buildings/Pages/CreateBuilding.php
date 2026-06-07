<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Buildings\Schemas\BuildingForm;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected ?string $heading = 'Nieuw gebouw';

    protected string $view = 'filament.dashboard.pages.buildings.create';

    protected function getSteps(): array
    {
        return BuildingForm::getWizardSteps();
    }

    public function hasSkippableSteps(): bool
    {
        return true;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['landlord_id'] = auth()->id();
        return $data;
    }
}
