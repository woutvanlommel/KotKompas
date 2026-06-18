<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Widgets\BuildingsOverviewTable;
use App\Filament\Dashboard\Widgets\ScoreOverview;
use App\Models\Building;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScoreOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    public function test_widget_shows_landlord_score_and_criteria_breakdown(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $rooms = Room::factory()->count(3)->for($building)->create(['title' => 'Zolderstudio']);

        // Three reviews clears the anonymity threshold so the breakdown shows.
        // Equal recency weights → plain averages: hygiene/value 4,0; size/comm 3,0.
        RoomReview::factory()->forRoom($rooms[0])->create([
            'score_hygiene' => 5, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 2,
        ]);
        RoomReview::factory()->forRoom($rooms[1])->create([
            'score_hygiene' => 3, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 4,
        ]);
        RoomReview::factory()->forRoom($rooms[2])->create([
            'score_hygiene' => 4, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 3,
        ]);

        // The observer wrote the cache to the DB via its own instance;
        // in a real request auth()->user() is freshly loaded.
        $landlord->refresh();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        // The widget pairs the overall verhuurderscore with the per-criterion
        // breakdown it rolls up from. Per-room titles must never appear — that
        // would de-anonymise the reviews.
        Livewire::test(ScoreOverview::class)
            ->assertSee('Verhuurderscore')
            ->assertSee('Per criterium')
            ->assertSee('Hygiëne')
            ->assertSee('Grootte')
            ->assertSee('Prijs-kwaliteit')
            ->assertSee('Communicatie')
            ->assertSee('4,0')
            ->assertSee('3,0')
            ->assertDontSee('Zolderstudio');
    }

    public function test_widget_hides_breakdown_below_anonymity_threshold(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $rooms = Room::factory()->count(2)->for($building)->create();

        // Two reviews is below MIN_REVIEWS_FOR_BREAKDOWN, so the criteria stay
        // masked behind a "still needed" progress message.
        RoomReview::factory()->forRoom($rooms[0])->create();
        RoomReview::factory()->forRoom($rooms[1])->create();

        $landlord->refresh();
        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ScoreOverview::class)
            ->assertSee('Verhuurderscore')
            ->assertSee('te gaan')
            ->assertDontSee('Hygiëne');
    }

    public function test_widget_shows_empty_state_without_reviews(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        Room::factory()->for($building)->create();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ScoreOverview::class)
            ->assertSee('Nog geen beoordelingen ontvangen')
            ->assertSee('beoordelingen nodig');
    }

    public function test_widget_is_hidden_for_non_landlords(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $this->actingAs($tenant);

        $this->assertFalse(ScoreOverview::canView());
    }

    public function test_buildings_table_shows_the_building_kotscore(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();
        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 3,
        ]);

        $lowBuilding = Building::factory()->create(['landlord_id' => $landlord->id]);
        $lowRoom = Room::factory()->for($lowBuilding)->create();
        RoomReview::factory()->forRoom($lowRoom)->create([
            'score_hygiene' => 2, 'score_size' => 2, 'score_value' => 2, 'score_communication' => 2,
        ]);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        // Each building shows its kotscore + review count; a weak score (< 3,5)
        // warms to the brand orange (#c2510a), a healthy one stays ink.
        Livewire::test(BuildingsOverviewTable::class)
            ->assertSee('Kotscore')
            ->assertSee('4,0')
            ->assertSee('2,0')
            ->assertSee('(1)')
            ->assertSee('text-[#c2510a]');
    }
}
