<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\Facility;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

trait HasFacilityActions
{
    public function editFacilitiesAction(): Action
    {
        $facilityOptions = Facility::orderBy('category')->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->pluck('name', 'id'))
            ->toArray();

        return Action::make('editFacilities')
            ->label('Bewerken')
            ->slideOver()
            ->form([
                Select::make('facility_ids')
                    ->label('Faciliteiten')
                    ->multiple()
                    ->live()
                    ->searchable()
                    ->options($facilityOptions)
                    ->default(fn () => $this->record->facilities->pluck('id')->toArray())
                    ->columnSpanFull(),

                Grid::make(1)
                    ->schema(fn (Get $get) => collect($get('facility_ids') ?? [])
                        ->map(function (int|string $id) {
                            $facility = Facility::find($id);

                            if (! $facility) {
                                return null;
                            }

                            return Fieldset::make($facility->name)
                                ->schema([
                                    TextInput::make("description_{$id}")
                                        ->label('Opmerking (optioneel)')
                                        ->maxLength(100)
                                        ->placeholder('bv. "op verdieping 2", "gedeeld met 3 kamers"')
                                        ->default(fn () => $this->record->facilities->find($id)?->pivot->description)
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull();
                        })
                        ->filter()
                        ->values()
                        ->toArray()
                    )
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $syncData = [];

                foreach ($data['facility_ids'] ?? [] as $id) {
                    $syncData[$id] = [
                        'description' => $data["description_{$id}"] ?? null,
                    ];
                }

                $this->record->facilities()->sync($syncData);
                $this->record->refresh();
            });
    }
}
