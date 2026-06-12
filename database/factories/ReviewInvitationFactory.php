<?php

namespace Database\Factories;

use App\Models\ReviewInvitation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReviewInvitation>
 */
class ReviewInvitationFactory extends Factory
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
            'token' => Str::random(64),
            'expires_at' => now()->addDays(ReviewInvitation::VALID_DAYS),
        ];
    }

    /**
     * Invitation for an existing room, with the building's landlord as the
     * landlord snapshot — exactly how ending a rental creates one.
     */
    public function forRoom(Room $room): static
    {
        return $this->state(fn () => [
            'room_id' => $room->id,
            'landlord_id' => $room->building->landlord_id,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['completed_at' => now()]);
    }
}
