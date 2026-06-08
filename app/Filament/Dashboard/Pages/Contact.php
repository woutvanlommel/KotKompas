<?php

namespace App\Filament\Dashboard\Pages;

use App\Mail\SupportContactMail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class Contact extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.dashboard.pages.contact';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Contact';

    protected static ?string $title = 'Contact met support';

    // Pinned to the bottom of the sidebar (just above the profile link) via a
    // SIDEBAR_NAV_END render hook in DashboardPanelProvider — not the auto nav.
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    /**
     * Only landlords see this page (nav + route guard both follow canAccess).
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('verhuurder') ?? false;
    }

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'name' => trim($user->name.' '.$user->lastname),
            'email' => $user->email,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()
                    ->description('Stuur een bericht naar het KotKompas-team. We antwoorden op je e-mailadres.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('subject')
                            ->label('Onderwerp')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('message')
                            ->label('Bericht')
                            ->required()
                            ->minLength(10)
                            ->maxLength(5000)
                            ->rows(6),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Verstuur bericht')
                ->action('send'),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        try {
            Mail::to(config('mail.support_address'))->send(new SupportContactMail(
                senderName: trim($user->name.' '.$user->lastname),
                senderEmail: $user->email,
                subjectLine: $data['subject'],
                body: $data['message'],
            ));
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Versturen mislukt')
                ->body('Er ging iets mis. Probeer het later opnieuw.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Bericht verstuurd')
            ->body('Bedankt! We antwoorden zo snel mogelijk op je e-mailadres.')
            ->success()
            ->send();

        // Reset the message fields, keep name/email prefilled.
        $this->form->fill([
            'name' => trim($user->name.' '.$user->lastname),
            'email' => $user->email,
        ]);
    }
}
