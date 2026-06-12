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

    /** Contracten aangemaakt door de verhuurder, gekoppeld via huurperiodes */
    public function getContracts(): Collection
    {
        return Document::whereHas('rentalPeriod', fn($q) => $q->where('user_id', auth()->id()))
            ->where('type', 'contract')
            ->with(['media', 'rentalPeriod.room.building'])
            ->latest()
            ->get();
    }

    public function getActiveRentalPeriods(): Collection
    {
        return RentalPeriod::where('user_id', auth()->id())
            ->with('room.building')
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->latest('start_date')
            ->get();
    }

    public function signContract(int $documentId): void
    {
        $contract = Document::whereHas('rentalPeriod', fn($q) => $q->where('user_id', auth()->id()))
            ->where('type', 'contract')
            ->where('status', 'draft')
            ->findOrFail($documentId);

        $contract->update([
            'status' => 'signed',
            'blocks' => array_merge($contract->blocks ?? [], [
                'signed_at' => now()->toIso8601String(),
                'signed_by' => auth()->id(),
            ]),
        ]);

        Notification::make()
            ->title('Contract ondertekend')
            ->success()
            ->send();
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
