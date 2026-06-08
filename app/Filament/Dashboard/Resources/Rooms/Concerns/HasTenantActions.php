<?php

namespace App\Filament\Dashboard\Resources\Rooms\Concerns;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

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
                $this->record->update([
                    'tenant_id' => $data['tenant_id'],
                    'status' => 'rented',
                ]);
                $this->record->refresh();
            });
    }

    public function unlinkTenantAction(): Action
    {
        return Action::make('unlinkTenant')
            ->label('Huurder ontkoppelen')
            ->requiresConfirmation()
            ->modalHeading('Huurder ontkoppelen')
            ->modalDescription('De huurder wordt ontkoppeld en de status wordt teruggezet naar "Beschikbaar".')
            ->modalSubmitActionLabel('Ontkoppelen')
            ->color('danger')
            ->action(function (): void {
                $this->record->update([
                    'tenant_id' => null,
                    'status' => 'available',
                ]);
                $this->record->refresh();
            });
    }
}
