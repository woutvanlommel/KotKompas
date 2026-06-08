<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Components\ImageUpload;
use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use App\Filament\Dashboard\Resources\Rooms\Schemas\RoomWizard;
use App\Models\Room;
use App\Services\FilamentNotificationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/** @property Room $record */
class ViewRoom extends ViewRecord
{
    protected static string $resource = RoomResource::class;

    protected string $view = 'filament.dashboard.pages.rooms.view';

    public ?int $buildingId = null;

    public int $galleryPage = 1;

    public function uploadGalleryAction(): Action
    {
        return Action::make('uploadGallery')
            ->label('Afbeeldingen toevoegen')
            ->icon('heroicon-o-photo')
            // ->slideOver()
            ->form([
                ImageUpload::make('gallery')
                    ->label('Afbeeldingen'),
            ])
            ->action(function (array $data): void {
                ImageUpload::append($this->record, $data['gallery'] ?? [], 'gallery');
                $this->record->refresh();
            });
    }

    public function setCoverAction(): Action
    {
        return Action::make('setCover')
            ->action(function (array $arguments): void {
                // Huidige cover terug naar gallery
                $currentCover = $this->record->getFirstMedia('cover');
                if ($currentCover) {
                    $currentCover->collection_name = 'gallery';
                    $currentCover->save();
                }

                // Geselecteerde foto instellen als cover
                $media = $this->record->getMedia('gallery')->firstWhere('id', $arguments['mediaId']);
                if ($media) {
                    $media->collection_name = 'cover';
                    $media->save();
                }

                $this->record->refresh();
            });
    }

    public function deleteGalleryImageAction(): Action
    {
        return Action::make('deleteGalleryImage')
            ->requiresConfirmation()
            ->modalHeading('Foto verwijderen')
            ->modalDescription('Weet je zeker dat je deze foto wil verwijderen? Dit kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Verwijderen')
            ->color('danger')
            ->action(function (array $arguments): void {
                // Zoek in zowel gallery als cover collection
                $media = $this->record->getMedia('gallery')->firstWhere('id', $arguments['mediaId'])
                    ?? $this->record->getFirstMedia('cover')?->id === $arguments['mediaId']
                    ? $this->record->getFirstMedia('cover')
                    : null;
                $media?->delete();
                $this->record->refresh();
            });
    }

    public function deleteSelectedGalleryImagesAction(): Action
    {
        return Action::make('deleteSelectedGalleryImages')
            ->requiresConfirmation()
            ->modalHeading('Foto\'s verwijderen')
            ->modalDescription(fn(array $arguments) => count($arguments['ids'] ?? []) . ' foto\'s worden permanent verwijderd. Dit kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Verwijderen')
            ->color('danger')
            ->action(function (array $arguments): void {
                $this->record->getMedia('gallery')
                    ->whereIn('id', $arguments['ids'] ?? [])
                    ->each->delete();
                $this->record->refresh();
                $this->dispatch('gallery-deleted');
            });
    }

    public function previousGalleryPage(): void
    {
        if ($this->galleryPage > 1) {
            $this->galleryPage--;
        }
    }

    public function nextGalleryPage(int $totalPages): void
    {
        if ($this->galleryPage < $totalPages) {
            $this->galleryPage++;
        }
    }

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
                ->successRedirectUrl(fn() => BuildingResource::getUrl('view', ['record' => $this->buildingId])),
        ];
    }
}
