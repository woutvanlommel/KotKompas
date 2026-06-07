<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Services\FilamentNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Inloggen';
    }

    /** Submit styled after Ynarchive's "Start a project" button (pill + chip + arrow-swap). */
    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(new HtmlString(Login::ctaLabel('Inloggen')))
            ->extraAttributes(['class' => 'kk-cta'])
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public static function ctaLabel(string $text): string
    {
        $arrow = '<path d="M8.90954 9.09046L9 3L2.90954 3.09046L2.90213 4.32367L6.86437 4.25391L2.55914 8.55914L3.44086 9.44086L7.74609 5.13563L7.68708 9.10862L8.90954 9.09046Z"/>';

        return '<span class="kk-cta-label">'.$text.'</span>'
            .'<span class="kk-cta-chip" aria-hidden="true">'
            .'<svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 12 12" fill="currentColor">'.$arrow.'</svg>'
            .'<svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 12 12" fill="currentColor">'.$arrow.'</svg>'
            .'</span>';
    }

    /**
     * The giant bottom-left word is the switch to the other auth page.
     * On login it sends you to registration.
     */
    public function getSubheading(): string|Htmlable
    {
        return new HtmlString(
            '<a href="'.filament()->getRegistrationUrl().'" class="kk-switch" aria-label="Ga naar registratie">'
                .'<span class="kk-switch-eyebrow">Nog geen account?</span>'
                .'<span class="kk-switch-row"><span class="kk-switch-word">Registreren</span>'
                .'<svg class="kk-arrow" viewBox="0 0 16 19" fill="none" aria-hidden="true"><path d="M7 18C7 18.5523 7.44772 19 8 19C8.55228 19 9 18.5523 9 18H7ZM8.70711 0.292893C8.31658 -0.0976311 7.68342 -0.0976311 7.29289 0.292893L0.928932 6.65685C0.538408 7.04738 0.538408 7.68054 0.928932 8.07107C1.31946 8.46159 1.95262 8.46159 2.34315 8.07107L8 2.41421L13.6569 8.07107C14.0474 8.46159 14.6805 8.46159 15.0711 8.07107C15.4616 7.68054 15.4616 7.04738 15.0711 6.65685L8.70711 0.292893ZM9 18L9 1H7L7 18H9Z" fill="currentColor"/></svg></span></a>'
        );
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null) {
            $user = filament()->auth()->user();
            $hour = now()->hour;

            $greeting = match (true) {
                $hour >= 5 && $hour < 11 => 'Goedemorgen',
                $hour >= 11 && $hour < 17 => 'Goedemiddag',
                $hour >= 17 && $hour < 23 => 'Goedenavond',
                default => 'Goedenacht',
            };

            FilamentNotificationService::success(
                title: "{$greeting}, {$user->name}!",
                body: 'Je bent succesvol ingelogd.',
                icon: 'heroicon-o-home',
            );
        }

        return $response;
    }

    protected function throwFailureValidationException(): never
    {
        FilamentNotificationService::danger(
            title: 'Inloggen mislukt',
            body: 'E-mailadres of wachtwoord is onjuist.',
            icon: 'heroicon-o-lock-closed',
        );

        parent::throwFailureValidationException();
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

    /**
     * Move the "wachtwoord vergeten?" link from the label hint (top-right of the
     * field) to below the input, where it reads as a deliberate secondary action.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->belowContent(filament()->hasPasswordReset()
                ? new HtmlString('<a href="'.filament()->getRequestPasswordResetUrl().'" class="fi-link kk-forgot" tabindex="-1">Wachtwoord vergeten?</a>')
                : null);
    }
}
