<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Concerns\SyncsMediaUploads;
use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = RoomResource::class;

    protected function getMediaCollections(): array
    {
        return ['cover', 'gallery'];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
