<?php

namespace App\Filament\Dashboard\Pages;

use App\Enums\DocumentVisibility;
use App\Jobs\ProcessDocumentOcr;
use App\Models\Document;
use App\Models\RentalPeriod;
use App\Models\Room;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
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
            ->with(['media', 'rentalPeriod.room.building', 'sharedWithUser', 'building'])
            ->latest()
            ->paginate(12);
    }

    /** Contracten — huurder ziet zijn eigen contracten, verhuurder ziet alle contracten van zijn kamers */
    public function getContracts(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('verhuurder')) {
            return Document::where('type', 'contract')
                ->whereHas('rentalPeriod.room.building', fn ($q) => $q->where('landlord_id', $user->id))
                ->with(['media', 'rentalPeriod.room.building', 'rentalPeriod.tenants'])
                ->latest()
                ->get();
        }

        return Document::whereHas('rentalPeriod.tenants', fn ($q) => $q->where('users.id', $user->id))
            ->where('type', 'contract')
            ->with(['media', 'rentalPeriod.room.building', 'rentalPeriod.tenants'])
            ->latest()
            ->get();
    }

    public function getActiveRentalPeriods(): Collection
    {
        return RentalPeriod::whereHas('tenants', fn ($q) => $q->where('users.id', auth()->id()))
            ->with('room.building')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
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
                    $query->whereHas('rentalPeriod.room.building', fn ($q) => $q->where('landlord_id', $user->id));
                } else {
                    $query->whereHas('rentalPeriod.tenants', fn ($q) => $q->where('users.id', $user->id));
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
                    'user_id' => $user->id,
                    'naam' => $user->full_name ?? $user->name,
                    'is_verhuurder' => $isVerhuurder,
                    'signed_at' => now()->toIso8601String(),
                ];

                $blocks['ondertekening']['handtekeningen'] = $handtekeningen;

                // Volledig ondertekend als verhuurder én alle huurders getekend hebben
                $tenantIds = $contract->rentalPeriod->tenants->pluck('id');
                $signedUserIds = collect($handtekeningen)->pluck('user_id');
                $verhuurderSigned = collect($handtekeningen)->where('is_verhuurder', true)->isNotEmpty();
                $allSigned = $verhuurderSigned && $tenantIds->diff($signedUserIds)->isEmpty();

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
                ->form($this->documentFormFields(withFile: true))
                ->action(function (array $data): void {
                    $user = auth()->user();

                    // Automatisch koppelen aan de actieve huurperiode van de huurder
                    $activePeriod = $this->getActiveRentalPeriods()->first();

                    $document = $user->documents()->create([
                        'name' => $data['name'],
                        'type' => $data['type'],
                        'visibility' => $data['visibility'],
                        'building_id' => $data['building_id'] ?? null,
                        'shared_with_user_id' => $data['shared_with_user_id'] ?? null,
                        'rental_period_id' => $activePeriod?->id,
                    ]);

                    if (! empty($data['file'])) {
                        $document
                            ->addMediaFromDisk($data['file'], 'public')
                            ->toMediaCollection('document');

                        $document->update(['ocr_status' => Document::OCR_PENDING]);
                        ProcessDocumentOcr::dispatch($document);
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

    /**
     * Gedeelde formuliervelden voor het uploaden én bewerken van een document.
     * Het bewerkformulier is identiek aan het uploadformulier, maar zonder bestand.
     *
     * @return array<int, Component>
     */
    private function documentFormFields(bool $withFile = false): array
    {
        return [
            TextInput::make('name')
                ->label('Naam')
                ->placeholder('bv. Inschrijvingsbewijs KU Leuven')
                ->maxLength(255)
                ->required(),

            ...($withFile ? [
                FileUpload::make('file')
                    ->label('Bestand')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('temp-uploads')
                    ->helperText('PDF of afbeelding, max. 10 MB'),
            ] : []),

            Select::make('type')
                ->label('Type')
                ->options([
                    'school' => 'Schooldocument',
                    'identity' => 'Identiteitsbewijs',
                    'other' => 'Andere',
                ])
                ->default('other')
                ->required(),

            Radio::make('visibility')
                ->label('Zichtbaarheid')
                ->options(function () {
                    if (auth()->user()->hasRole('verhuurder')) {
                        return [
                            DocumentVisibility::Private->value => 'Privé (enkel ikzelf)',
                            DocumentVisibility::Building->value => 'Hele gebouw (alle huidige huurders)',
                            DocumentVisibility::User->value => 'Specifieke student',
                        ];
                    }

                    return [
                        DocumentVisibility::Private->value => 'Privé (enkel ikzelf)',
                        DocumentVisibility::Landlord->value => 'Delen met mijn verhuurder',
                    ];
                })
                ->default(DocumentVisibility::Private->value)
                ->required()
                ->live(),

            Select::make('building_id')
                ->label('Gebouw')
                ->options(fn () => auth()->user()->buildings()->pluck('name', 'id'))
                ->visible(fn (Get $get) => in_array($get('visibility'), [
                    DocumentVisibility::Building->value,
                    DocumentVisibility::User->value,
                ], true))
                ->required(fn (Get $get) => in_array($get('visibility'), [
                    DocumentVisibility::Building->value,
                    DocumentVisibility::User->value,
                ], true))
                ->live(),

            Select::make('shared_with_user_id')
                ->label('Student')
                ->options(function (Get $get) {
                    $buildingId = $get('building_id');
                    if (! $buildingId) {
                        return [];
                    }

                    return Room::query()
                        ->where('building_id', $buildingId)
                        ->get()
                        ->flatMap(fn (Room $room) => $room->activeTenants())
                        ->unique('id')
                        ->pluck('full_name', 'id');
                })
                ->visible(fn (Get $get) => $get('visibility') === DocumentVisibility::User->value)
                ->required(fn (Get $get) => $get('visibility') === DocumentVisibility::User->value),
        ];
    }

    public function editDocumentAction(): Action
    {
        return Action::make('editDocument')
            ->label('Bewerken')
            ->modalHeading('Document bewerken')
            ->slideOver()
            ->fillForm(function (array $arguments): array {
                $document = auth()->user()->documents()->findOrFail($arguments['documentId']);

                return [
                    'name' => $document->name,
                    'type' => $document->type,
                    'visibility' => $document->visibility->value,
                    'building_id' => $document->building_id,
                    'shared_with_user_id' => $document->shared_with_user_id,
                ];
            })
            ->form($this->documentFormFields())
            ->action(function (array $data, array $arguments): void {
                $document = auth()->user()->documents()->findOrFail($arguments['documentId']);

                $document->update([
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'visibility' => $data['visibility'],
                    'building_id' => $data['building_id'] ?? null,
                    'shared_with_user_id' => $data['shared_with_user_id'] ?? null,
                ]);

                Notification::make()
                    ->title('Document bijgewerkt')
                    ->success()
                    ->send();
            });
    }

    /** Documenten die anderen met mij hebben gedeeld (alleen-lezen). */
    public function getSharedWithMe(): Collection
    {
        return Document::sharedWith(auth()->user())
            ->with(['user', 'sharedWithUser'])
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
            'school' => 'Schooldocument',
            'identity' => 'Identiteitsbewijs',
            'contract' => 'Contract',
            default => 'Andere',
        };
    }

    public static function getTypeColor(string $type): string
    {
        return match ($type) {
            'school' => 'blue',
            'identity' => 'amber',
            'contract' => 'green',
            default => 'gray',
        };
    }
}
