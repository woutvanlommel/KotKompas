<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Concerns\SyncsMediaUploads;
use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = RoomResource::class;

    protected function getMediaCollections(): array
    {
        return ['cover', 'gallery'];
    }

    public function mount(): void
    {
        parent::mount();

        if (request()->has('building_id')) {
            $this->form->fill([
                'building_id' => request()->query('building_id'),
            ]);
        }
    }
}
