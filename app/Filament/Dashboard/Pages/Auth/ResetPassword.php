<?php

namespace App\Filament\Dashboard\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Support\HtmlString;

class ResetPassword extends BaseResetPassword
{
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
}
