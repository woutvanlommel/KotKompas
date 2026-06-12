<?php

namespace App\Filament\Dashboard\Pages;

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
use Illuminate\Support\Collection;

class Documents extends Page
{
    protected string $view = 'filament.dashboard.pages.documents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    protected static ?string $navigationLabel = 'Mijn documenten';

    protected static ?string $title = 'Mijn documenten';

    protected static ?int $navigationSort = 3;

    public string $viewMode = 'card'; // 'card' | 'list'

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('huurder'), 403);
    }

    public function getDocuments(): Collection
    {
        return auth()->user()
            ->documents()
            ->with(['media', 'rentalPeriod.room.building'])
            ->latest()
            ->get();
    }

    public function getActiveRentalPeriods(): Collection
    {
        return RentalPeriod::where('user_id', auth()->id())
            ->with('room.building')
            ->whereNull('end_date')
            ->orWhere('end_date', '>=', now())
            ->latest('start_date')
            ->get();
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

                    Select::make('rental_period_id')
                        ->label('Koppel aan huurperiode')
                        ->options(
                            $this->getActiveRentalPeriods()
                                ->mapWithKeys(fn(RentalPeriod $rp) => [
                                    $rp->id => $rp->room->building->street . ' — Kamer ' . $rp->room->room_number,
                                ])
                        )
                        ->placeholder('Geen koppeling')
                        ->nullable(),

                    Toggle::make('is_public')
                        ->label('Zichtbaar voor verhuurder')
                        ->helperText('Zet aan om dit document te delen met de verhuurder van de gekoppelde kamer')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $user = auth()->user();

                    $document = $user->documents()->create([
                        'name'             => $data['name'],
                        'type'             => $data['type'],
                        'is_public'        => $data['is_public'],
                        'rental_period_id' => $data['rental_period_id'] ?? null,
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
