<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use App\Filament\Dashboard\Resources\Rooms\Schemas\RoomWizard;
use App\Models\Room;
use App\Services\FilamentNotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/** @property Room $record */
class ViewRoom extends ViewRecord
{
    protected static string $resource = RoomResource::class;

    protected string $view = 'filament.dashboard.pages.rooms.view';

    public function getBreadcrumbs(): array
    {
        $building = $this->record->building;

        return [
            BuildingResource::getUrl('index') => 'Gebouwen',
            BuildingResource::getUrl('view', ['record' => $building->id]) => $building->name,
            '#' => $this->record->title ?: 'Kamer ' . $this->record->room_number,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Bewerken')
                ->slideOver()
                ->form([RoomWizard::make()])
                ->successNotification(null)
                ->after(function () {
                    FilamentNotificationService::success(
                        'Kamer bijgewerkt',
                        "{$this->record->title} is bijgewerkt.",
                        icon: 'heroicon-o-rectangle-stack'
                    );
                }),
            DeleteAction::make()
                ->successNotification(null)
                ->after(function () {
                    FilamentNotificationService::success(
                        'Kamer verwijderd',
                        'De kamer is verwijderd.',
                        icon: 'heroicon-o-rectangle-stack'
                    );
                })
                ->successRedirectUrl(fn () => BuildingResource::getUrl('view', ['record' => $this->record->building_id])),
        ];
    }
}
