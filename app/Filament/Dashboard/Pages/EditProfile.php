<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.dashboard.pages.edit-profile';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'email' => auth()->user()->email,
            'phone' => auth()->user()->phone,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Contact details')->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique('users', 'email', ignorable: auth()->user()),
                    TextInput::make('phone')
                        ->label('Phone number')
                        ->tel(),
                ]),
                Section::make('Change password')
                    ->description('Leave blank to keep your current password.')
                    ->schema([
                        TextInput::make('password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->nullable()
                            ->dehydrated(fn ($state) => filled($state)),
                        TextInput::make('password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->nullable()
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(Profile::getUrl()),
            Action::make('save')
                ->label('Save changes')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();
        $updateData = [
            'email' => $data['email'],
            'phone' => $data['phone'],
        ];

        if (filled($data['password'] ?? null)) {
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();

        $this->redirect(Profile::getUrl());
    }
}
