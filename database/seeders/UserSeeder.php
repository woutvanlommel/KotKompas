<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'lastname' => 'KotKompas',
            'email' => 'admin@kotkompas.be',
            'phone' => '+32470345678',
            'date_of_birth' => '2003-01-01',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $verhuurder = User::create([
            'name' => 'Verhuurder',
            'lastname' => 'KotKompas',
            'email' => 'verhuurder@kotkompas.be',
            'phone' => '+32470345679',
            'date_of_birth' => '2003-01-02',
            'password' => Hash::make('password'),
        ]);
        $verhuurder->assignRole('verhuurder');

        $huurder = User::create([
            'name' => 'Huurder',
            'lastname' => 'KotKompas',
            'email' => 'huurder@kotkompas.be',
            'phone' => '+32470345680',
            'date_of_birth' => '2003-01-03',
            'password' => Hash::make('password'),
        ]);
        $huurder->assignRole('huurder');
    }
}
