<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'landlord_id' => User::factory(),
            'name' => fake()->company().' Residentie',
            'street' => fake()->streetName(),
            'house_number' => fake()->numberBetween(1, 200),
            'postal_code' => fake()->numberBetween(1000, 9999),
            'city' => fake()->randomElement(['Hasselt', 'Gent', 'Leuven', 'Antwerpen']),
            'country' => 'België',
        ];
    }
}
