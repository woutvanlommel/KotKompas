<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\CostType;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

trait HasCostActions
{
    public function editCostsAction(): Action
    {
        $costTypeOptions = CostType::orderBy('category')->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(fn($items) => $items->pluck('name', 'id'))
            ->toArray();

        return Action::make('editCosts')
            ->label('Bewerken')
            ->slideOver()
            ->form([
                Select::make('cost_type_ids')
                    ->label('Kostensoorten')
                    ->multiple()
                    ->live()
                    ->searchable()
                    ->options($costTypeOptions)
                    ->default(fn() => $this->record->costTypes->pluck('id')->toArray())
                    ->columnSpanFull(),

                Grid::make(1)
                    ->schema(
                        fn(Get $get) => collect($get('cost_type_ids') ?? [])
                            ->map(function (int|string $id) {
                                $costType = CostType::find($id);

                                if (! $costType) {
                                    return null;
                                }

                                return Fieldset::make($costType->name)
                                    ->schema([
                                        Select::make("frequency_{$id}")
                                            ->label('Frequentie')
                                            ->options([
                                                'monthly'  => 'Maandelijks',
                                                'yearly'   => 'Jaarlijks',
                                                'one_time' => 'Eenmalig',
                                            ])
                                            ->required()
                                            ->default(fn() => $this->record->costTypes->find($id)?->pivot->frequency ?? 'monthly'),
                                        TextInput::make("amount_{$id}")
                                            ->label('Bedrag')
                                            ->numeric()
                                            ->prefix('€')
                                            ->placeholder('Leeg laten indien variabel')
                                            ->default(fn() => $this->record->costTypes->find($id)?->pivot->amount),
                                        Toggle::make("is_variable_{$id}")
                                            ->label('Variabel (geen vaste prijs)')
                                            ->default(fn() => (bool) ($this->record->costTypes->find($id)?->pivot->is_variable ?? false))
                                            ->columnSpanFull(),
                                        TextInput::make("description_{$id}")
                                            ->label('Opmerking')
                                            ->maxLength(100)
                                            ->placeholder('Optioneel')
                                            ->default(fn() => $this->record->costTypes->find($id)?->pivot->description)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
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

                foreach ($data['cost_type_ids'] ?? [] as $id) {
                    $syncData[$id] = [
                        'frequency'   => $data["frequency_{$id}"] ?? 'monthly',
                        'amount'      => $data["amount_{$id}"] ?? null,
                        'is_variable' => (bool) ($data["is_variable_{$id}"] ?? false),
                        'description' => $data["description_{$id}"] ?? null,
                    ];
                }

                $this->record->costTypes()->sync($syncData);
                $this->record->refresh();
            });
    }
}
