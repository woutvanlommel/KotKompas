<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasCostActions;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasDocumentActions;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasFacilityActions;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasGalleryActions;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasRoomInfoActions;
use App\Filament\Dashboard\Resources\Rooms\Concerns\HasTenantActions;
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
    use HasCostActions;
    use HasDocumentActions;
    use HasFacilityActions;
    use HasGalleryActions;
    use HasRoomInfoActions;
    use HasTenantActions;

    protected static string $resource = RoomResource::class;

    protected string $view = 'filament.dashboard.pages.rooms.view';

    public ?int $buildingId = null;

    public function getBreadcrumbs(): array
    {
        $building = $this->record->building;

        return [
            BuildingResource::getUrl('index') => 'Gebouwen',
            BuildingResource::getUrl('view', ['record' => $building->id]) => $building->name,
            '#' => $this->record->title ?: 'Kamer '.$this->record->room_number,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Bewerken')
                ->slideOver()
                ->form([RoomWizard::make($this->record->building)])
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
                ->before(function () {
                    $this->buildingId = $this->record->building_id;
                })
                ->after(function () {
                    FilamentNotificationService::success(
                        'Kamer verwijderd',
                        'De kamer is verwijderd.',
                        icon: 'heroicon-o-rectangle-stack'
                    );
                })
                ->successRedirectUrl(fn () => BuildingResource::getUrl('view', ['record' => $this->buildingId])),
        ];
    }
}
