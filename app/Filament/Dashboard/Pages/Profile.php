<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.dashboard.pages.profile';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

protected function getHeaderActions(): array
{
    return [
        Action::make('edit')
            ->label('Edit profile')
            ->slideOver()
            ->fillForm(fn () => [
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone,
            ])
            ->form([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->rules(['unique:users,email,' . auth()->id()]),
                TextInput::make('phone')
                    ->label('Phone number')
                    ->tel(),
            ])
            ->action(function (array $data): void {
                auth()->user()->update([
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ]);

                Notification::make()
                    ->title('Profile updated')
                    ->success()
                    ->send();
            }),
    ];
}

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
                        ->disabled(),
                    TextInput::make('lastname')
                        ->label('Last name')
                        ->disabled(),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->disabled(),
                    TextInput::make('phone')
                        ->label('Phone number')
                        ->tel()
                        ->disabled(),
                ]),
            ])
            ->statePath('data');
    }
}
