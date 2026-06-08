<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    protected static string $resource = RoomResource::class;

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
