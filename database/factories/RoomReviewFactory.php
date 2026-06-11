<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomReview>
 */
class RoomReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'landlord_id' => User::factory(),
            'tenant_id' => User::factory(),
            'score_hygiene' => fake()->numberBetween(1, 5),
            'score_size' => fake()->numberBetween(1, 5),
            'score_value' => fake()->numberBetween(1, 5),
            'score_communication' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Beoordeling voor een bestaand kot, met de verhuurder van het gebouw
     * als landlord-snapshot — zoals de enquête hem straks ook aanmaakt.
     */
    public function forRoom(Room $room): static
    {
        return $this->state(fn () => [
            'room_id' => $room->id,
            'landlord_id' => $room->building->landlord_id,
        ]);
    }
}
