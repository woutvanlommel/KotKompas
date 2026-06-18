<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Components\ImageUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.dashboard.pages.profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Profiel';

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Profiel bewerken')
                ->slideOver()
                ->fillForm(fn () => [
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone,
                    'avatar' => ImageUpload::existingPaths(auth()->user(), 'avatar'),
                ])
                ->form([
                    ImageUpload::make('avatar', false)
                        ->label('Profielfoto'),
                    TextInput::make('email')
                        ->label('E-mailadres')
                        ->email()
                        ->required()
                        ->rules(['unique:users,email,'.auth()->id()]),
                    TextInput::make('phone')
                        ->label('Telefoonnummer')
                        ->tel(),
                ])
                ->action(function (array $data): void {
                    $user = auth()->user();

                    ImageUpload::sync($user, (array) ($data['avatar'] ?? []), 'avatar');

                    $user->update([
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                    ]);

                    Notification::make()
                        ->title('Profiel bijgewerkt')
                        ->success()
                        ->send();
                }),
            Action::make('changePassword')
                ->label('Wachtwoord wijzigen')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Wachtwoord wijzigen')
                ->modalDescription('We sturen een resetlink naar je e-mailadres.')
                ->modalSubmitActionLabel('Verstuur resetlink')
                ->action(function (): void {
                    Password::sendResetLink(['email' => auth()->user()->email]);

                    Notification::make()
                        ->title('Resetlink verstuurd')
                        ->body('Check je inbox voor de link om je wachtwoord te wijzigen.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
