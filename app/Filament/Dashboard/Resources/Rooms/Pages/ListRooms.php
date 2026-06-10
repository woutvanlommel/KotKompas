<?php

namespace App\Filament\Dashboard\Resources\Rooms\Pages;

use App\Filament\Dashboard\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->whereHas('building', fn (Builder $q) => $q->where('landlord_id', auth()->id()));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
