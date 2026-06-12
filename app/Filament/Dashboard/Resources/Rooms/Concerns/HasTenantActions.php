<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\RentalPeriod;
use App\Models\ReviewInvitation;
use App\Models\User;
use App\Services\FilamentNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

trait HasTenantActions
{
    // ── Status ────────────────────────────────────────────────────────────────

    public function updateStatusAction(): Action
    {
        return Action::make('updateStatus')
            ->label('Status wijzigen')
            ->form([
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Beschikbaar',
                        'rented' => 'Verhuurd',
                        'maintenance' => 'Onderhoud',
                        'archived' => 'Gearchiveerd',
                    ])
                    ->default(fn () => $this->record->status)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $this->record->update(['status' => $data['status']]);
                $this->record->refresh();
            });
    }

    // ── Hoofdhuurder koppelen / wijzigen ──────────────────────────────────────

    public function linkTenantAction(): Action
    {
        return Action::make('linkTenant')
            ->label(fn () => $this->record->tenant ? 'Hoofdhuurder wijzigen' : 'Huurder koppelen')
            ->form([
                Select::make('tenant_id')
                    ->label('Huurder')
                    ->placeholder('Zoek op naam of e-mail…')
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search): array => User::role('huurder')
                            ->where(fn ($q) => $q
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                            )
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (User $u) => [$u->id => "{$u->full_name} ({$u->email})"])
                            ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->full_name)
                    ->default(fn () => $this->record->tenant_id)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $room = $this->record;
                $newTenant = User::findOrFail($data['tenant_id']);

                DB::transaction(function () use ($room, $newTenant) {
                    // Sluit lopende periode(s) af
                    $room->rentalPeriods()
                        ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                        ->each(fn (RentalPeriod $rp) => $rp->update([
                            'end_date' => now()->subDay()->toDateString(),
                        ]));

                    // Nieuwe periode aanmaken — datums worden ingevuld via het contract
                    $period = RentalPeriod::create([
                        'room_id' => $room->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => null,
                    ]);

                    $period->tenants()->attach($newTenant->id, ['is_primary' => true]);

                    // tenant_id op room bewaren voor RoomObserver (review invitations)
                    $room->update([
                        'tenant_id' => $newTenant->id,
                        'status' => 'rented',
                    ]);
                });

                $this->record->refresh();
            });
    }

    // ── Medehuurder toevoegen ─────────────────────────────────────────────────

    public function addCoTenantAction(): Action
    {
        return Action::make('addCoTenant')
            ->label('Medehuurder toevoegen')
            ->form(function () {
                $activePeriod = $this->record->rentalPeriods()
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                $alreadyLinked = $activePeriod
                    ? $activePeriod->tenants()->pluck('users.id')->toArray()
                    : [];

                return [
                    Select::make('tenant_id')
                        ->label('Medehuurder')
                        ->placeholder('Zoek op naam of e-mail…')
                        ->searchable()
                        ->getSearchResultsUsing(
                            fn (string $search): array => User::role('huurder')
                                ->whereNotIn('id', $alreadyLinked)
                                ->where(fn ($q) => $q
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('lastname', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                )
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->id => "{$u->full_name} ({$u->email})"])
                                ->all()
                        )
                        ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->full_name)
                        ->required(),
                ];
            })
            ->action(function (array $data): void {
                $room = $this->record;

                $activePeriod = $room->rentalPeriods()
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                if (! $activePeriod) {
                    Notification::make()
                        ->title('Geen actieve huurperiode')
                        ->body('Koppel eerst een hoofdhuurder voor je een medehuurder toevoegt.')
                        ->warning()
                        ->send();

                    return;
                }

                $activePeriod->tenants()->attach($data['tenant_id'], ['is_primary' => false]);

                $tenant = User::find($data['tenant_id']);

                Notification::make()
                    ->title('Medehuurder toegevoegd')
                    ->body("{$tenant?->full_name} is als medehuurder gekoppeld aan deze kamer.")
                    ->success()
                    ->send();

                $this->record->refresh();
            });
    }

    // ── Medehuurder verwijderen ───────────────────────────────────────────────

    public function removeCoTenantAction(): Action
    {
        return Action::make('removeCoTenant')
            ->label('Medehuurder verwijderen')
            ->requiresConfirmation()
            ->modalHeading('Medehuurder verwijderen')
            ->modalDescription('De medehuurder wordt van deze huurperiode verwijderd.')
            ->modalSubmitActionLabel('Verwijderen')
            ->color('danger')
            ->action(function (array $arguments): void {
                $tenantId = $arguments['tenantId'] ?? null;

                if (! $tenantId) {
                    return;
                }

                $activePeriod = $this->record->rentalPeriods()
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->latest('start_date')
                    ->first();

                if (! $activePeriod) {
                    return;
                }

                // Voorkom verwijderen van de hoofdhuurder via deze actie
                $isPrimary = $activePeriod->tenants()
                    ->wherePivot('user_id', $tenantId)
                    ->wherePivot('is_primary', true)
                    ->exists();

                if ($isPrimary) {
                    Notification::make()
                        ->title('Kan hoofdhuurder niet verwijderen')
                        ->body('Gebruik "Ontkoppelen" om de hoofdhuurder en de volledige huurperiode te beëindigen.')
                        ->warning()
                        ->send();

                    return;
                }

                $activePeriod->tenants()->detach($tenantId);

                $this->record->refresh();
            });
    }

    // ── Hoofdhuurder ontkoppelen (sluit periode) ──────────────────────────────

    public function unlinkTenantAction(): Action
    {
        return Action::make('unlinkTenant')
            ->label('Huurperiode beëindigen')
            ->requiresConfirmation()
            ->modalHeading('Huurperiode beëindigen')
            ->modalDescription('De volledige huurperiode (hoofd- én medehuurders) wordt afgesloten. De status wordt teruggezet naar "Beschikbaar". De ex-huurder krijgt een beoordelingslink.')
            ->modalSubmitActionLabel('Beëindigen')
            ->color('danger')
            ->action(function (): void {
                $room = $this->record;
                $tenant = $room->tenant;

                DB::transaction(function () use ($room) {
                    // Actieve periode(s) afsluiten
                    $room->rentalPeriods()
                        ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                        ->each(fn (RentalPeriod $rp) => $rp->update([
                            'end_date' => now()->toDateString(),
                        ]));

                    // tenant_id wissen → triggert RoomObserver → review invitation
                    $room->update([
                        'tenant_id' => null,
                        'status' => 'available',
                    ]);
                });

                $room->refresh();

                if (! $tenant) {
                    return;
                }

                $hasInvitation = ReviewInvitation::query()
                    ->where('room_id', $room->id)
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('completed_at')
                    ->exists();

                FilamentNotificationService::success(
                    'Huurperiode beëindigd',
                    $hasInvitation
                        ? "Deel de beoordelingslink met {$tenant->name} — je vindt hem bij Status & Huurder."
                        : "{$tenant->name} beoordeelde dit kot al eerder; er is geen nieuwe beoordelingslink nodig.",
                );
            });
    }

    // ── Beoordelingslink heruitgeven ──────────────────────────────────────────

    public function reissueReviewInvitationAction(): Action
    {
        return Action::make('reissueReviewInvitation')
            ->label('Nieuwe link maken')
            ->requiresConfirmation()
            ->modalHeading('Nieuwe beoordelingslink')
            ->modalDescription('De verlopen link vervalt definitief; de ex-huurder kan het kot met de nieuwe link beoordelen.')
            ->modalSubmitActionLabel('Maak nieuwe link')
            ->action(function (array $arguments): void {
                $invitation = ReviewInvitation::query()
                    ->whereKey($arguments['invitation'] ?? null)
                    ->where('room_id', $this->record->id)
                    ->first();

                if (! $invitation || $invitation->tenant_id === null) {
                    return;
                }

                if (ReviewInvitation::issueFor($this->record, $invitation->tenant_id)) {
                    FilamentNotificationService::success(
                        'Nieuwe beoordelingslink aangemaakt',
                        'Deel hem met de ex-huurder — je vindt hem bij Status & Huurder.',
                    );
                }
            });
    }
}
