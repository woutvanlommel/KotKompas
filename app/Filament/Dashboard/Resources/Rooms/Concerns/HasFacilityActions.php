<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\Facility;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;

trait HasFacilityActions
{
    public function editFacilitiesAction(): Action
    {
        $existingIds = $this->record->facilities->pluck('id')->toArray();

        $categorySections = Facility::orderBy('category')->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(function ($facilities, string $category) use ($existingIds) {
                $key = 'facility_cat_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($category));

                $selectedInCategory = $facilities
                    ->pluck('id')
                    ->intersect($existingIds)
                    ->values()
                    ->toArray();

                return Section::make($category)
                    ->schema([
                        CheckboxList::make($key)
                            ->hiddenLabel()
                            ->options($facilities->pluck('name', 'id')->toArray())
                            ->columns(2)
                            ->columnSpanFull()
                            ->default($selectedInCategory),
                    ])
                    ->collapsible()
                    ->collapsed(empty($selectedInCategory))
                    ->columnSpanFull();
            })
            ->values()
            ->toArray();

        return Action::make('editFacilities')
            ->label('Bewerken')
            ->slideOver()
            ->form($categorySections)
            ->action(function (array $data): void {
                $allIds = collect($data)
                    ->filter(fn($v, $k) => str_starts_with($k, 'facility_cat_'))
                    ->flatten()
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $this->record->facilities()->sync($allIds);
                $this->record->refresh();
            });
    }
}
