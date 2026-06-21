<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BuildingSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(CostTypeSeeder::class);
        $this->call(RoomCostSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(SubscriptionSeeder::class);
        $this->call(ScoreSeeder::class);
    }
}
