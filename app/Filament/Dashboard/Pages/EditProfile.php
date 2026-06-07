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
        $user = auth()->user();

        $this->form->fill([
            'name' => $user->name,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label('First name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('lastname')
                        ->label('Last name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->rules(['unique:users,email,'.auth()->id()]),
                    TextInput::make('phone')
                        ->label('Phone number')
                        ->tel(),
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
        $user->update([
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();

        $this->redirect(Profile::getUrl());
    }
}
