<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\Document;
use App\Models\RentalPeriod;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

trait HasDocumentActions
{
    public function createContractAction(): Action
    {
        return Action::make('createContract')
            ->label('Contract aanmaken')
            ->icon('heroicon-o-document-plus')
            ->slideOver()
            ->form(function () {
                $room     = $this->record;
                $building = $room->building;
                $tenant   = $room->activeTenant() ?? $room->tenant;

                // Zoek of maak de actieve huurperiode
                $activePeriod = $room->rentalPeriods()
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                return [
                    TextInput::make('name')
                        ->label('Naam contract')
                        ->default('Huurcontract — Kamer ' . $room->room_number)
                        ->required()
                        ->maxLength(255),

                    TextInput::make('tenant_name')
                        ->label('Huurder')
                        ->default($tenant?->full_name)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('address')
                        ->label('Adres')
                        ->default($building->street . ' ' . $building->house_number . ', ' . $building->postal_code . ' ' . $building->city)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('price')
                        ->label('Huurprijs per maand (€)')
                        ->default($room->price_per_month)
                        ->numeric()
                        ->required(),

                    TextInput::make('deposit')
                        ->label('Borgsom (€)')
                        ->default($room->deposit_amount)
                        ->numeric()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Huurperiode start')
                        ->default($activePeriod?->start_date)
                        ->required(),

                    DatePicker::make('end_date')
                        ->label('Huurperiode einde')
                        ->default($activePeriod?->end_date)
                        ->helperText('Leeg laten voor onbepaalde duur'),

                    Textarea::make('special_conditions')
                        ->label('Bijzondere voorwaarden')
                        ->rows(4)
                        ->placeholder('Optioneel: specifieke afspraken, huisregels, ...'),

                    Select::make('rental_period_id')
                        ->label('Koppel aan huurperiode')
                        ->options(
                            $room->rentalPeriods()
                                ->with('tenant')
                                ->get()
                                ->mapWithKeys(fn (RentalPeriod $rp) => [
                                    $rp->id => ($rp->tenant?->full_name ?? 'Onbekend')
                                        . ' (' . $rp->start_date->format('d/m/Y') . ' – '
                                        . ($rp->end_date?->format('d/m/Y') ?? 'heden') . ')',
                                ])
                        )
                        ->default($activePeriod?->id)
                        ->nullable()
                        ->placeholder('Geen koppeling'),
                ];
            })
            ->action(function (array $data): void {
                $room     = $this->record;
                $building = $room->building;
                $landlord = auth()->user();
                $tenant   = $room->activeTenant() ?? $room->tenant;

                // Blokken opbouwen met alle gegevens
                $blocks = [
                    'room'    => [
                        'number'  => $room->room_number,
                        'type'    => $room->type,
                        'address' => $building->street . ' ' . $building->house_number
                            . ', ' . $building->postal_code . ' ' . $building->city,
                    ],
                    'landlord' => [
                        'name'  => $landlord->full_name,
                        'email' => $landlord->email,
                        'phone' => $landlord->phone,
                    ],
                    'tenant' => [
                        'name'  => $tenant?->full_name,
                        'email' => $tenant?->email,
                        'phone' => $tenant?->phone,
                    ],
                    'rental' => [
                        'price'              => $data['price'],
                        'deposit'            => $data['deposit'],
                        'start_date'         => $data['start_date'],
                        'end_date'           => $data['end_date'] ?? null,
                        'special_conditions' => $data['special_conditions'] ?? null,
                    ],
                    'created_at' => now()->toIso8601String(),
                ];

                Document::create([
                    'user_id'          => $landlord->id,
                    'name'             => $data['name'],
                    'type'             => 'contract',
                    'is_public'        => true, // huurder moet het kunnen zien
                    'rental_period_id' => $data['rental_period_id'] ?? null,
                    'status'           => 'draft',
                    'blocks'           => $blocks,
                ]);

                Notification::make()
                    ->title('Contract aangemaakt')
                    ->body('De huurder kan het contract nu bekijken en ondertekenen.')
                    ->success()
                    ->send();
            });
    }

    public function getTenantDocuments(): \Illuminate\Support\Collection
    {
        $tenant = $this->record->activeTenant() ?? $this->record->tenant;

        if (! $tenant) {
            return collect();
        }

        // Publieke docs van de huurder, gekoppeld aan huurperiodes van deze kamer
        return Document::where('user_id', $tenant->id)
            ->where('is_public', true)
            ->where('type', '!=', 'contract')
            ->whereHas('rentalPeriod', fn ($q) => $q->where('room_id', $this->record->id))
            ->with('media')
            ->latest()
            ->get();
    }

    public function getRoomContracts(): \Illuminate\Support\Collection
    {
        return Document::where('type', 'contract')
            ->whereHas('rentalPeriod', fn ($q) => $q->where('room_id', $this->record->id))
            ->orWhere(fn ($q) => $q
                ->where('type', 'contract')
                ->where('user_id', auth()->id())
                ->whereNull('rental_period_id')
            )
            ->with(['media', 'rentalPeriod.tenant'])
            ->latest()
            ->get();
    }
}
