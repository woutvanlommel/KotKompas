<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    public function mount($record = null): void
    {
        abort_if($record->landlord_id !== auth()->id(), 403);
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
