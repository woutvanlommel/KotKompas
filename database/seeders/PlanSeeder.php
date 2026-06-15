<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'description' => 'Voor wie net begint met verhuren.',
                'features' => ['Tot 5 kamers', 'Basis kotprofiel', 'Berichten met huurders'],
                'sort_order' => 1,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Voor de groeiende verhuurder.',
                'features' => ['Tot 20 kamers', 'Reviews & KotScore', 'Documentbeheer', 'Voorrang in zoekresultaten'],
                'sort_order' => 2,
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'description' => 'Voor professionele verhuurders.',
                'features' => ['Onbeperkt kamers', 'Alles uit Pro', 'Statistieken & inzichten', 'Priority support'],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
