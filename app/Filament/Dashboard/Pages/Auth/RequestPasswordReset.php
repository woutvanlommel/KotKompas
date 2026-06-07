<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Services\FilamentNotificationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function getHeading(): string
    {
        return 'Wachtwoord vergeten';
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label(new HtmlString(Login::ctaLabel('E-mail verzenden')))
            ->extraAttributes(['class' => 'kk-cta'])
            ->submit('request');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    protected function getSentNotification(string $status): ?Notification
    {
        FilamentNotificationService::success(
            title: 'E-mail verzonden',
            body: 'Controleer je inbox voor de resetlink.',
            icon: 'heroicon-o-envelope',
        );

        return null;
    }

    protected function getFailureNotification(string $status): ?Notification
    {
        FilamentNotificationService::danger(
            title: 'E-mailadres niet gevonden',
            body: 'Controleer of je het juiste e-mailadres hebt ingegeven.',
            icon: 'heroicon-o-exclamation-circle',
        );

        return null;
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
