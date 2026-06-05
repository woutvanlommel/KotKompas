<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class Register extends BaseRegister
{
    /**
     * Holds the chosen role between mutateFormDataBeforeRegister and handleRegistration.
     * Not persisted as a user column.
     */
    protected string $pendingRole = 'huurder';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getLastnameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getDateOfBirthFormComponent(),
                $this->getRoleFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
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
                'huurder' => 'Huurder (student/ketter)',
                'verhuurder' => 'Verhuurder (eigenaar/agent)',
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

        return $user;
    }
}
