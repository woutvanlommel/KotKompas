<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait HasDocumentActions
{
    public function createContractAction(): Action
    {
        return Action::make('createContract')
            ->label('Contract aanmaken')
            ->icon('heroicon-o-document-plus')
            ->slideOver()
            ->form(function () {
                $room = $this->record;
                $building = $room->building;
                $activePeriod = $room->rentalPeriods()
                    ->with('tenants')
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                $tenantNames = $activePeriod
                    ? $activePeriod->tenants->map->full_name->join(', ')
                    : '—';

                $address = $building->street.' '.$building->house_number
                    .', '.$building->postal_code.' '.$building->city;

                return [
                    // ── Info (read-only context) ───────────────────────────
                    Placeholder::make('info_kamer')
                        ->label('Kamer')
                        ->content("Kamer {$room->room_number} — {$address}"),

                    Placeholder::make('info_huurder')
                        ->label('Huurder(s)')
                        ->content($tenantNames),

                    // ── Naam (aanpasbaar) ──────────────────────────────────
                    TextInput::make('name')
                        ->label('Naam contract')
                        ->default("Huurcontract — Kamer {$room->room_number}")
                        ->required()
                        ->maxLength(255),

                    // ── Huurperiode ────────────────────────────────────────
                    DatePicker::make('start_date')
                        ->label('Startdatum')
                        ->default($activePeriod?->start_date ?? now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $months = (int) $get('duration_months');
                            if ($months > 0 && $state) {
                                $set('end_date', Carbon::parse($state)
                                    ->addMonths($months)->subDay()->format('Y-m-d'));
                            }
                        }),

                    Select::make('duration_months')
                        ->label('Duur huurperiode')
                        ->options([
                            9 => '9 maanden',
                            10 => '10 maanden',
                            11 => '11 maanden',
                            12 => '12 maanden',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?int $state) {
                            if ($state && $get('start_date')) {
                                $set('end_date', Carbon::parse($get('start_date'))
                                    ->addMonths($state)->subDay()->format('Y-m-d'));
                            }
                        }),

                    DatePicker::make('end_date')
                        ->label('Einddatum (automatisch)')
                        ->disabled()
                        ->dehydrated(true),

                    // ── Optioneel ──────────────────────────────────────────
                    Textarea::make('special_conditions')
                        ->label('Bijzondere voorwaarden')
                        ->rows(3)
                        ->placeholder('Optioneel: specifieke afspraken, huisregels, …'),
                ];
            })
            ->action(function (array $data): void {
                $room = $this->record;
                $building = $room->building;
                $landlord = auth()->user();
                $activePeriod = $room->rentalPeriods()
                    ->with('tenants')
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                $tenants = $activePeriod?->tenants ?? collect();

                $blocks = [
                    'partijen' => [
                        'verhuurder' => [
                            'naam' => $landlord->full_name,
                            'email' => $landlord->email,
                            'tel' => $landlord->phone,
                        ],
                        'huurders' => $tenants->map(fn ($u) => [
                            'user_id' => $u->id,
                            'naam' => $u->full_name,
                            'email' => $u->email,
                            'tel' => $u->phone,
                            'is_primary' => (bool) $u->pivot->is_primary,
                        ])->values()->toArray(),
                    ],
                    'goed' => [
                        'adres' => $building->street.' '.$building->house_number
                            .', '.$building->postal_code.' '.$building->city,
                        'kamer' => $room->room_number,
                        'type' => $room->type,
                        'oppervlakte' => $room->surface_m2,
                        'gemeubeld' => $room->is_furnished,
                    ],
                    'huurperiode' => [
                        'duur_maanden' => $data['duration_months'],
                        'start' => $data['start_date'],
                        'einde' => $data['end_date'] ?? null,
                    ],
                    'financieel' => [
                        'huurprijs' => $room->price_per_month,
                        'borgsom' => $room->deposit_amount,
                    ],
                    'bijzondere_voorwaarden' => $data['special_conditions'] ?? null,
                    'wettelijk' => [
                        'toepasselijk_recht' => 'Vlaamse Codex Wonen 2021 — Studentenhuurovereenkomst',
                    ],
                    'ondertekening' => [
                        'aangemaakt_op' => now()->toIso8601String(),
                        'aangemaakt_door' => $landlord->full_name,
                        'huurder_getekend' => null,
                    ],
                ];

                // Start- én einddatum van de huurperiode bepalen via het contract
                if ($activePeriod) {
                    $activePeriod->update([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'] ?? null,
                    ]);
                }

                Document::create([
                    'user_id' => $landlord->id,
                    'name' => $data['name'],
                    'type' => 'contract',
                    'is_public' => true,
                    'rental_period_id' => $activePeriod?->id,
                    'status' => 'draft',
                    'blocks' => $blocks,
                ]);

                Notification::make()
                    ->title('Contract aangemaakt')
                    ->body('De huurder kan het contract nu bekijken en ondertekenen.')
                    ->success()
                    ->send();
            });
    }

    public function getTenantDocuments(): Collection
    {
        $tenants = $this->record->activeTenants();

        if ($tenants->isEmpty()) {
            return collect();
        }

        return Document::whereIn('user_id', $tenants->pluck('id'))
            ->where('is_public', true)
            ->where('type', '!=', 'contract')
            ->whereHas('rentalPeriod', fn ($q) => $q->where('room_id', $this->record->id))
            ->with('media')
            ->latest()
            ->get();
    }

    public function deleteContractAction(): Action
    {
        return Action::make('deleteContract')
            ->requiresConfirmation()
            ->modalHeading('Contract verwijderen')
            ->modalDescription('Ben je zeker? Een ondertekend contract verwijderen kan juridische gevolgen hebben.')
            ->modalSubmitActionLabel('Ja, verwijderen')
            ->modalCancelActionLabel('Annuleren')
            ->modalIcon('heroicon-o-trash')
            ->color('danger')
            ->action(function (array $arguments): void {
                $documentId = $arguments['documentId'] ?? null;

                $contract = Document::where('type', 'contract')
                    ->whereHas('rentalPeriod.room.building', fn ($q) => $q->where('landlord_id', auth()->id()))
                    ->findOrFail($documentId);

                $contract->delete();

                Notification::make()
                    ->title('Contract verwijderd')
                    ->success()
                    ->send();
            });
    }

    public function signContractAction(): Action
    {
        return Action::make('signContract')
            ->requiresConfirmation()
            ->modalHeading('Contract ondertekenen')
            ->modalDescription('Door te bevestigen verklaar je dit contract als verhuurder gelezen en goedgekeurd te hebben. Deze actie kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Ja, ondertekenen')
            ->modalCancelActionLabel('Annuleren')
            ->modalIcon('heroicon-o-pencil')
            ->color('success')
            ->action(function (array $arguments): void {
                $documentId = $arguments['documentId'] ?? null;
                $user = auth()->user();

                $contract = Document::where('type', 'contract')
                    ->whereHas('rentalPeriod', fn ($q) => $q->where('room_id', $this->record->id))
                    ->where('status', 'draft')
                    ->with('rentalPeriod.tenants')
                    ->findOrFail($documentId);

                $blocks = $contract->blocks ?? [];
                $handtekeningen = $blocks['ondertekening']['handtekeningen'] ?? [];

                if (collect($handtekeningen)->contains('user_id', $user->id)) {
                    return;
                }

                $handtekeningen[] = [
                    'user_id' => $user->id,
                    'naam' => $user->full_name ?? $user->name,
                    'is_verhuurder' => true,
                    'signed_at' => now()->toIso8601String(),
                ];

                $blocks['ondertekening']['handtekeningen'] = $handtekeningen;

                // Volledig ondertekend als verhuurder + alle huurders getekend hebben
                $tenantIds = $contract->rentalPeriod->tenants->pluck('id');
                $signedUserIds = collect($handtekeningen)->pluck('user_id');
                $allSigned = $signedUserIds->contains($user->id)
                    && $tenantIds->diff($signedUserIds)->isEmpty();

                $contract->update([
                    'status' => $allSigned ? 'signed' : 'draft',
                    'blocks' => $blocks,
                ]);

                Notification::make()
                    ->title('Handtekening geregistreerd')
                    ->body($allSigned
                        ? 'Alle partijen hebben ondertekend. Het contract is nu volledig ondertekend.'
                        : 'Jouw handtekening is opgeslagen. Het contract wacht nog op de huurder(s).')
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public function getRoomContracts(): Collection
    {
        return Document::where('type', 'contract')
            ->where(fn ($q) => $q
                ->whereHas('rentalPeriod', fn ($q2) => $q2->where('room_id', $this->record->id))
                ->orWhere(fn ($q2) => $q2
                    ->where('user_id', auth()->id())
                    ->whereNull('rental_period_id')
                )
            )
            ->with(['media', 'rentalPeriod.tenants'])
            ->latest()
            ->get();
    }
}
