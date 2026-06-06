<?php

namespace App\Filament\Dashboard\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
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
}
