<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\Document;
use App\Models\RentalPeriod;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

class Documents extends Page
{
    protected string $view = 'filament.dashboard.pages.documents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    protected static ?string $navigationLabel = 'Mijn documenten';

    protected static ?string $title = 'Mijn documenten';

    protected static ?int $navigationSort = 3;

    use WithPagination;

    public string $viewMode = 'card'; // 'card' | 'list'

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['huurder', 'verhuurder']), 403);
    }

    /** Eigen geüploade documenten (geen contracten), 12 per pagina */
    public function getDocuments(): LengthAwarePaginator
    {
        return auth()->user()
            ->documents()
            ->where('type', '!=', 'contract')
            ->with(['media', 'rentalPeriod.room.building'])
            ->latest()
            ->paginate(12);
    }

    /** Contracten — huurder ziet zijn eigen contracten, verhuurder ziet alle contracten van zijn kamers */
    public function getContracts(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('verhuurder')) {
            return Document::where('type', 'contract')
                ->whereHas('rentalPeriod.room.building', fn($q) => $q->where('landlord_id', $user->id))
                ->with(['media', 'rentalPeriod.room.building', 'rentalPeriod.tenants'])
                ->latest()
                ->get();
        }

        return Document::whereHas('rentalPeriod.tenants', fn($q) => $q->where('users.id', $user->id))
            ->where('type', 'contract')
            ->with(['media', 'rentalPeriod.room.building', 'rentalPeriod.tenants'])
            ->latest()
            ->get();
    }

    public function getActiveRentalPeriods(): Collection
    {
        return RentalPeriod::whereHas('tenants', fn($q) => $q->where('users.id', auth()->id()))
            ->with('room.building')
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->latest('start_date')
            ->get();
    }

    public function signContractAction(): Action
    {
        return Action::make('signContract')
            ->requiresConfirmation()
            ->modalHeading('Contract ondertekenen')
            ->modalDescription('Door te bevestigen verklaar je dit contract gelezen en goedgekeurd te hebben. Deze actie kan niet ongedaan gemaakt worden.')
            ->modalSubmitActionLabel('Ja, ondertekenen')
            ->modalCancelActionLabel('Annuleren')
            ->modalIcon('heroicon-o-pencil')
            ->color('success')
            ->action(function (array $arguments): void {
                $documentId = $arguments['documentId'] ?? null;
                $user = auth()->user();

                $query = Document::where('type', 'contract')->where('status', 'draft');

                if ($user->hasRole('verhuurder')) {
                    $query->whereHas('rentalPeriod.room.building', fn($q) => $q->where('landlord_id', $user->id));
                } else {
                    $query->whereHas('rentalPeriod.tenants', fn($q) => $q->where('users.id', $user->id));
                }

                $contract = $query->with('rentalPeriod.tenants')->findOrFail($documentId);

                $blocks = $contract->blocks ?? [];
                $handtekeningen = $blocks['ondertekening']['handtekeningen'] ?? [];

                // Voorkom dubbel ondertekenen
                if (collect($handtekeningen)->contains('user_id', $user->id)) {
                    return;
                }

                $isVerhuurder = $user->hasRole('verhuurder');
                $handtekeningen[] = [
                    'user_id'        => $user->id,
                    'naam'           => $user->full_name ?? $user->name,
                    'is_verhuurder'  => $isVerhuurder,
                    'signed_at'      => now()->toIso8601String(),
                ];

                $blocks['ondertekening']['handtekeningen'] = $handtekeningen;

                // Volledig ondertekend als verhuurder én alle huurders getekend hebben
                $tenantIds     = $contract->rentalPeriod->tenants->pluck('id');
                $signedUserIds = collect($handtekeningen)->pluck('user_id');
                $verhuurderSigned = collect($handtekeningen)->where('is_verhuurder', true)->isNotEmpty();
                $allSigned     = $verhuurderSigned && $tenantIds->diff($signedUserIds)->isEmpty();

                $contract->update([
                    'status' => $allSigned ? 'signed' : 'draft',
                    'blocks' => $blocks,
                ]);

                Notification::make()
                    ->title('Handtekening geregistreerd')
                    ->body($allSigned
                        ? 'Alle huurders hebben ondertekend. Het contract is nu volledig ondertekend.'
                        : 'Jouw handtekening is opgeslagen. Het contract wacht nog op de andere huurder(s).')
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Document uploaden')
                ->icon('heroicon-o-arrow-up-tray')
                ->slideOver()
                ->form([
                    TextInput::make('name')
                        ->label('Naam')
                        ->placeholder('bv. Inschrijvingsbewijs KU Leuven')
                        ->maxLength(255)
                        ->required(),

                    FileUpload::make('file')
                        ->label('Bestand')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(10240)
                        ->disk('public')
                        ->directory('temp-uploads')
                        ->helperText('PDF of afbeelding, max. 10 MB'),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'school'   => 'Schooldocument',
                            'identity' => 'Identiteitsbewijs',
                            'other'    => 'Andere',
                        ])
                        ->default('other')
                        ->required(),

                    Toggle::make('is_public')
                        ->label('Zichtbaar voor verhuurder')
                        ->helperText('Zet aan om dit document te delen met de verhuurder van de gekoppelde kamer')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $user = auth()->user();

                    // Automatisch koppelen aan de actieve huurperiode van de huurder
                    $activePeriod = $this->getActiveRentalPeriods()->first();

                    $document = $user->documents()->create([
                        'name'             => $data['name'],
                        'type'             => $data['type'],
                        'is_public'        => $data['is_public'],
                        'rental_period_id' => $activePeriod?->id,
                    ]);

                    if (! empty($data['file'])) {
                        $document
                            ->addMediaFromDisk($data['file'], 'public')
                            ->toMediaCollection('document');
                    }

                    Notification::make()
                        ->title('Document geüpload')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function toggleViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    public function togglePublic(int $documentId): void
    {
        $document = auth()->user()->documents()->findOrFail($documentId);
        $document->update(['is_public' => ! $document->is_public]);

        Notification::make()
            ->title($document->is_public ? 'Document nu zichtbaar voor verhuurder' : 'Document nu privé')
            ->success()
            ->send();
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
                $user = auth()->user();

                abort_unless($user->hasRole('verhuurder'), 403);

                $documentId = $arguments['documentId'] ?? null;

                $contract = Document::where('type', 'contract')
                    ->where(fn ($q) => $q
                        ->whereHas('rentalPeriod.room.building', fn ($q2) => $q2->where('landlord_id', $user->id))
                        ->orWhere('user_id', $user->id)
                    )
                    ->findOrFail($documentId);

                $contract->delete();

                Notification::make()
                    ->title('Contract verwijderd')
                    ->success()
                    ->send();
            });
    }

    public function deleteDocument(int $documentId): void
    {
        $document = auth()->user()->documents()->findOrFail($documentId);
        $document->delete();

        Notification::make()
            ->title('Document verwijderd')
            ->success()
            ->send();
    }

    public static function getTypeLabel(string $type): string
    {
        return match ($type) {
            'school'   => 'Schooldocument',
            'identity' => 'Identiteitsbewijs',
            'contract' => 'Contract',
            default    => 'Andere',
        };
    }

    public static function getTypeColor(string $type): string
    {
        return match ($type) {
            'school'   => 'blue',
            'identity' => 'amber',
            'contract' => 'green',
            default    => 'gray',
        };
    }
}
