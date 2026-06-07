<?php

namespace App\Filament\Dashboard\Resources\Buildings\Pages;

use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nieuw gebouw')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['landlord_id'] = auth()->id();
                    return $data;
                }),
        ];
    }

    protected function getQuery(): Builder
    {
        return parent::getQuery()->where('landlord_id', auth()->id());
    }
}
