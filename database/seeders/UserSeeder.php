<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'lastname' => 'KotKompas',
            'email' => 'admin@kotkompas.com',
            'phone' => '+32470345678',
            'date_of_birth' => '2003-01-01',
            'password' => Hash::make('password'),
        ]);
    }
}
