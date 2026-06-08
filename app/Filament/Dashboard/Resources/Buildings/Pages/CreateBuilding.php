<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Concerns\SyncsMediaUploads;
use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use App\Services\FilamentNotificationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['landlord_id'] = auth()->id();

        // Extract image uploads before the model is created
        $this->extractUploadsFromData($data);

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        FilamentNotificationService::success(
            'Gebouw aangemaakt',
            "{$this->record->name} is succesvol aangemaakt.",
            icon: 'heroicon-o-building-office-2'
        );

        return null;
    }
}
