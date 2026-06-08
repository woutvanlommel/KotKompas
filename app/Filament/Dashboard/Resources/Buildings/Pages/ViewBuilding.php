<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Models\Building;
use App\Services\FilamentNotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
