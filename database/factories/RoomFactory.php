<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'room_number' => (string) fake()->numberBetween(1, 50),
            'type' => fake()->randomElement(['kamer', 'studio', 'appartement']),
            'title' => 'Kot '.fake()->word(),
            'price_per_month' => fake()->numberBetween(350, 950),
            'costs_included' => fake()->boolean(),
            'surface_m2' => fake()->numberBetween(12, 60),
            'is_furnished' => fake()->boolean(),
            'status' => 'available',
        ];
    }
}
