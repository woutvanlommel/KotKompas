<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Services\FilamentNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use SensitiveParameter;

class ResetPassword extends BaseResetPassword
{
    /**
     * Tracks whether a rate-limit notification was already sent so we don't
     * accidentally show a generic danger notification on top of it.
     */
    private bool $wasRateLimited = false;

    public function mount(?string $email = null, #[SensitiveParameter] ?string $token = null): void
    {
        // Skip the parent's authenticated redirect so logged-in users
        // can still use a reset link from their profile page.
        $this->token = $token ?? request()->query('token');

        $this->form->fill([
            'email' => $email ?? request()->query('email'),
        ]);
    }

    public function getHeading(): string
    {
        return 'Nieuw wachtwoord';
    }

    public function getResetPasswordFormAction(): Action
    {
        return Action::make('resetPassword')
            ->label(new HtmlString(Login::ctaLabel('Wachtwoord opslaan')))
            ->extraAttributes(['class' => 'kk-cta'])
            ->submit('resetPassword');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        $response = parent::resetPassword();

        if ($this->wasRateLimited) {
            return $response;
        }

        // Remove the parent's default inline notification, replace with ours.
        session()->forget('filament.notifications');

        if ($response !== null) {
            FilamentNotificationService::success(
                title: 'Wachtwoord opgeslagen',
                body: 'Je kan nu inloggen met je nieuw wachtwoord.',
                icon: 'heroicon-o-lock-open',
            );
        } else {
            FilamentNotificationService::danger(
                title: 'Resetlink ongeldig',
                body: 'De link is verlopen of al gebruikt. Vraag een nieuwe aan.',
                icon: 'heroicon-o-exclamation-circle',
            );
        }

        return $response;
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        $this->wasRateLimited = true;

        FilamentNotificationService::warning(
            title: 'Te veel pogingen',
            body: "Wacht {$exception->secondsUntilAvailable} seconden voor je het opnieuw probeert.",
            icon: 'heroicon-o-clock',
        );

        return null;
    }
}
