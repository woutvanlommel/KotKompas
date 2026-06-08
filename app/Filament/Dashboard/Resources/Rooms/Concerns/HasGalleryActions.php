<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Filament\Components\ImageUpload;
use Filament\Actions\Action;

trait HasGalleryActions
{
    public int $galleryPage = 1;

    public function uploadGalleryAction(): Action
    {
        return Action::make('uploadGallery')
            ->label('Afbeeldingen toevoegen')
            ->icon('heroicon-o-photo')
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
                $currentCover = $this->record->getFirstMedia('cover');
                if ($currentCover) {
                    $currentCover->collection_name = 'gallery';
                    $currentCover->save();
                }

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
            ->modalDescription(fn (array $arguments) => count($arguments['ids'] ?? []) . ' foto\'s worden permanent verwijderd. Dit kan niet ongedaan gemaakt worden.')
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
}
