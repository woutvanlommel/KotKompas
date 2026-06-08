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

    public array $selectedMedia = [];

    public bool $selectMode = false;

    protected mixed $pendingCover = [];

    protected mixed $pendingGallery = [];

    public function deleteGalleryImageAction(): Action
    {
        return Action::make('deleteGalleryImage')
            ->requiresConfirmation()
            ->modalHeading('Foto verwijderen')
            ->modalDescription('Weet je zeker dat je deze foto wil verwijderen? Dit kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Verwijderen')
            ->color('danger')
            ->action(function (array $arguments): void {
                $media = $this->record->getMedia('gallery')->firstWhere('id', $arguments['mediaId']);
                $media?->delete();
            });
    }

    public function deleteSelectedGalleryImagesAction(): Action
    {
        return Action::make('deleteSelectedGalleryImages')
            ->requiresConfirmation()
            ->modalHeading('Foto\'s verwijderen')
            ->modalDescription(fn () => count($this->selectedMedia).' foto\'s worden permanent verwijderd. Dit kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Verwijderen')
            ->color('danger')
            ->action(function (): void {
                foreach ($this->record->getMedia('gallery')->whereIn('id', $this->selectedMedia) as $media) {
                    $media->delete();
                }
                $this->selectedMedia = [];
                $this->selectMode = false;
            });
    }

    public function toggleMediaSelection(int $mediaId): void
    {
        if (in_array($mediaId, $this->selectedMedia, true)) {
            $this->selectedMedia = array_values(array_filter($this->selectedMedia, fn ($id) => $id !== $mediaId));
        } else {
            $this->selectedMedia[] = $mediaId;
        }
    }

    public function cancelSelectMode(): void
    {
        $this->selectMode = false;
        $this->selectedMedia = [];
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
                ->mutateRecordDataUsing(function (array $data): array {
                    $data['cover'] = ImageUpload::existingPaths($this->record, 'cover');
                    $data['gallery'] = ImageUpload::existingPaths($this->record, 'gallery');

                    return $data;
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $this->pendingCover = $data['cover'] ?? [];
                    $this->pendingGallery = $data['gallery'] ?? [];
                    unset($data['cover'], $data['gallery']);

                    return $data;
                })
                ->successNotification(null)
                ->after(function () {
                    ImageUpload::sync($this->record, is_array($this->pendingCover) ? $this->pendingCover : [$this->pendingCover], 'cover');
                    ImageUpload::sync($this->record, is_array($this->pendingGallery) ? $this->pendingGallery : [], 'gallery');

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
