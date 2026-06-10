<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoom(array $roomAttributes = [], array $buildingAttributes = []): Room
    {
        $landlord = User::factory()->create();

        $building = Building::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'city' => 'Hasselt',
        ], $buildingAttributes));

        return Room::factory()->create(array_merge([
            'building_id' => $building->id,
            'status' => 'available',
            'title' => 'Zonnig kot',
        ], $roomAttributes));
    }

    public function test_it_suggests_cities_matching_the_query(): void
    {
        $this->makeRoom();

        $response = $this->getJson('/koten/suggesties?q=Has');

        $response->assertOk()
            ->assertJsonPath('suggestions.0.type', 'stad')
            ->assertJsonPath('suggestions.0.label', 'Hasselt');
    }

    public function test_it_suggests_rooms_matching_the_query(): void
    {
        $this->makeRoom(['title' => 'Penthouse studio']);

        $response = $this->getJson('/koten/suggesties?q=Pent');

        $response->assertOk()
            ->assertJsonPath('suggestions.0.type', 'kot')
            ->assertJsonPath('suggestions.0.label', 'Penthouse studio');
    }

    public function test_it_ignores_unavailable_rooms(): void
    {
        $this->makeRoom(['status' => 'rented']);

        $this->getJson('/koten/suggesties?q=Has')
            ->assertOk()
            ->assertJsonCount(0, 'suggestions');
    }

    public function test_it_validates_the_query(): void
    {
        $this->getJson('/koten/suggesties')->assertUnprocessable();
        $this->getJson('/koten/suggesties?q=a')->assertUnprocessable();
        $this->getJson('/koten/suggesties?q='.str_repeat('a', 61))->assertUnprocessable();
    }

    public function test_it_strips_html_from_the_query(): void
    {
        $this->makeRoom();

        // "<b>Has</b>" → "Has" na strip_tags; mag gewoon resultaten geven, geen injectie.
        $this->getJson('/koten/suggesties?q='.urlencode('<b>Has</b>'))
            ->assertOk()
            ->assertJsonPath('suggestions.0.label', 'Hasselt');
    }
}
