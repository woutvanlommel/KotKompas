<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Models\User;
use App\Services\FilamentNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use SensitiveParameter;

class Register extends BaseRegister
{
    /**
     * Holds the chosen role between mutateFormDataBeforeRegister and handleRegistration.
     * Not persisted as a user column.
     */
    protected string $pendingRole = 'huurder';

    public function getHeading(): string
    {
        return 'Registreren';
    }

    /** Submit styled after Ynarchive's "Start a project" button (pill + chip + arrow-swap). */
    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(new HtmlString(Login::ctaLabel('Registreren')))
            ->extraAttributes(['class' => 'kk-cta'])
            ->submit('register');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    /**
     * The giant bottom-left word is the switch to the other auth page.
     * On registration it sends you back to login.
     */
    public function getSubheading(): string|Htmlable
    {
        return new HtmlString(
            '<a href="'.filament()->getLoginUrl().'" class="kk-switch" aria-label="Ga naar inloggen">'
                .'<span class="kk-switch-eyebrow">Al een account?</span>'
                .'<span class="kk-switch-row"><span class="kk-switch-word">Inloggen</span>'
                .'<svg class="kk-arrow" viewBox="0 0 16 19" fill="none" aria-hidden="true"><path d="M7 18C7 18.5523 7.44772 19 8 19C8.55228 19 9 18.5523 9 18H7ZM8.70711 0.292893C8.31658 -0.0976311 7.68342 -0.0976311 7.29289 0.292893L0.928932 6.65685C0.538408 7.04738 0.538408 7.68054 0.928932 8.07107C1.31946 8.46159 1.95262 8.46159 2.34315 8.07107L8 2.41421L13.6569 8.07107C14.0474 8.46159 14.6805 8.46159 15.0711 8.07107C15.4616 7.68054 15.4616 7.04738 15.0711 6.65685L8.70711 0.292893ZM9 18L9 1H7L7 18H9Z" fill="currentColor"/></svg></span></a>'
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    $this->getNameFormComponent(),
                    $this->getLastnameFormComponent(),
                    $this->getEmailFormComponent(),
                    $this->getPhoneFormComponent(),
                ]),
                $this->getRoleFormComponent(),
                $this->getDateOfBirthFormComponent(),
                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ]),
            ]);
    }

    protected function getLastnameFormComponent(): Component
    {
        return TextInput::make('lastname')
            ->label('Achternaam')
            ->required()
            ->maxLength(255);
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Telefoonnummer')
            ->tel()
            ->required()
            ->maxLength(30);
    }

    protected function getDateOfBirthFormComponent(): Component
    {
        return DatePicker::make('date_of_birth')
            ->label('Geboortedatum')
            ->visible(fn (Get $get): bool => $get('role') === 'huurder')
            ->required(fn (Get $get): bool => $get('role') === 'huurder')
            ->maxDate(now()->subYears(16)->toDateString());
    }

    protected function getRoleFormComponent(): Component
    {
        return Radio::make('role')
            ->label('Ik ben een …')
            ->options([
                'huurder' => 'Huurder (student)',
                'verhuurder' => 'Verhuurder (eigenaar)',
            ])
            ->live()
            ->required();
    }

    /**
     * Stash the role before stripping it from the data array that gets passed
     * to ::create(). The role field is marked dehydrated(false) on the form
     * component so Livewire doesn't persist it, but the value is still present
     * in the raw form state at this point.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(#[SensitiveParameter] array $data): array
    {
        if (isset($data['role']) && in_array($data['role'], ['huurder', 'verhuurder'], true)) {
            $this->pendingRole = $data['role'];
        }

        unset($data['role']);

        return $data;
    }

    /**
     * Create the user, then assign the Spatie role captured in mutateFormDataBeforeRegister.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        $user = User::create($data);

        $user->assignRole($this->pendingRole);

        FilamentNotificationService::success(
            title: 'Welkom bij KotKompas!',
            body: 'Je account is aangemaakt. Veel plezier op KotKompas!.',
            icon: 'heroicon-o-user-circle',
        );

        return $user;
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        FilamentNotificationService::warning(
            title: 'Te veel pogingen',
            body: "Wacht {$exception->secondsUntilAvailable} seconden voor je het opnieuw probeert.",
            icon: 'heroicon-o-clock',
        );

        return null;
    }
}
