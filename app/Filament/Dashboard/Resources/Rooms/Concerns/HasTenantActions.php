<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\ReviewInvitation;
use App\Models\User;
use App\Services\FilamentNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

trait HasTenantActions
{
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

    public function linkTenantAction(): Action
    {
        return Action::make('linkTenant')
            ->label(fn () => $this->record->tenant ? 'Huurder wijzigen' : 'Huurder koppelen')
            ->form([
                Select::make('tenant_id')
                    ->label('Huurder')
                    ->placeholder('Zoek op naam of e-mail…')
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search): array => User::role('huurder')
                            ->where(fn ($q) => $q
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                            )
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                            ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                    ->default(fn () => $this->record->tenant_id)
                    ->required(),
            ])
            ->action(function (array $data): void {
                // In one transaction: a swap A→B ends A's rental, so the
                // RoomObserver also creates a survey invitation for A here.
                DB::transaction(fn () => $this->record->update([
                    'tenant_id' => $data['tenant_id'],
                    'status' => 'rented',
                ]));
                $this->record->refresh();
            });
    }

    public function unlinkTenantAction(): Action
    {
        return Action::make('unlinkTenant')
            ->label('Huurder ontkoppelen')
            ->requiresConfirmation()
            ->modalHeading('Huurder ontkoppelen')
            ->modalDescription('De huurder wordt ontkoppeld en de status wordt teruggezet naar "Beschikbaar". De huurder krijgt een link om het kot te beoordelen.')
            ->modalSubmitActionLabel('Ontkoppelen')
            ->color('danger')
            ->action(function (): void {
                $tenant = $this->record->tenant;

                // In one transaction with the invitation (RoomObserver): if
                // that fails, the tenant stays linked and can retry.
                DB::transaction(fn () => $this->record->update([
                    'tenant_id' => null,
                    'status' => 'available',
                ]));
                $this->record->refresh();

                if (! $tenant) {
                    return;
                }

                $hasInvitation = ReviewInvitation::query()
                    ->where('room_id', $this->record->id)
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('completed_at')
                    ->exists();

                FilamentNotificationService::success(
                    'Huurder ontkoppeld',
                    $hasInvitation
                        ? "Deel de beoordelingslink met {$tenant->name} — je vindt hem bij Status & Huurder."
                        : "{$tenant->name} beoordeelde dit kot al eerder; er is geen nieuwe beoordelingslink nodig.",
                );
            });
    }

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
