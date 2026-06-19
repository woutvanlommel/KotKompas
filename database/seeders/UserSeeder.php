<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'lastname' => 'KotKompas',
            'email' => 'admin@kotkompas.be',
            'phone' => '+32470345678',
            'date_of_birth' => '2003-01-01',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Huurder
        $huurder = User::create([
            'name' => 'Huurder',
            'lastname' => 'KotKompas',
            'email' => 'huurder@kotkompas.be',
            'phone' => '+32470345680',
            'date_of_birth' => '2003-01-03',
            'password' => Hash::make('password'),
        ]);
        $huurder->assignRole('huurder');

        // 5 verhuurders. De eerste behoudt het bekende e-mailadres (wordt o.a.
        // door HighlightTestSeeder gebruikt). De plan-koppeling gebeurt in
        // SubscriptionSeeder; de volgorde hier bepaalt welke verhuurder welk
        // plan krijgt.
        $verhuurders = [
            ['name' => 'Verhuurder', 'lastname' => 'KotKompas', 'email' => 'verhuurder@kotkompas.be', 'phone' => '+32470345679', 'dob' => '1979-04-12'],
            ['name' => 'Sofie', 'lastname' => 'Vermeulen', 'email' => 'sofie.vermeulen@kotkompas.be', 'phone' => '+32471112233', 'dob' => '1985-09-23'],
            ['name' => 'Karel', 'lastname' => 'Peeters', 'email' => 'karel.peeters@kotkompas.be', 'phone' => '+32472223344', 'dob' => '1972-02-08'],
            ['name' => 'Inge', 'lastname' => 'Maes', 'email' => 'inge.maes@kotkompas.be', 'phone' => '+32473334455', 'dob' => '1990-11-30'],
            ['name' => 'Bram', 'lastname' => 'Janssens', 'email' => 'bram.janssens@kotkompas.be', 'phone' => '+32474445566', 'dob' => '1988-06-17'],
        ];

        foreach ($verhuurders as $data) {
            $verhuurder = User::create([
                'name' => $data['name'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'date_of_birth' => $data['dob'],
                'password' => Hash::make('password'),
            ]);
            $verhuurder->assignRole('verhuurder');
        }
    }
}
