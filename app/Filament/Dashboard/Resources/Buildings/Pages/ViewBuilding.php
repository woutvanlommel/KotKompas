<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

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
            EditAction::make()
                ->label('Bewerken')
                ->slideOver(),
            DeleteAction::make(),
        ];
    }
}
