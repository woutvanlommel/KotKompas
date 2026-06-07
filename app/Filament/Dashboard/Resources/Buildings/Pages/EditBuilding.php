<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Buildings\Schemas\BuildingForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected ?string $heading = 'Gebouw bewerken';

    protected string $view = 'filament.dashboard.pages.buildings.edit';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        abort_if($this->record->landlord_id !== auth()->id(), 403);
    }

    protected function getSteps(): array
    {
        return BuildingForm::getWizardSteps();
    }

    public function hasSkippableSteps(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
