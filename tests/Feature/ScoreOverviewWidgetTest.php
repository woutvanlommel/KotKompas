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

    public function test_widget_shows_landlord_and_building_scores(): void
    {
        $landlord = $this->landlord();
        $topBuilding = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Residentie Park']);
        $otherBuilding = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Residentie Centrum']);
        $topRoom = Room::factory()->for($topBuilding)->create(['title' => 'Zolderstudio']);
        $otherRoom = Room::factory()->for($otherBuilding)->create();

        RoomReview::factory()->forRoom($topRoom)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5, 'score_communication' => 5,
        ]);
        RoomReview::factory()->forRoom($otherRoom)->create([
            'score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 2,
        ]);

        // The observer wrote the cache to the DB via its own instance;
        // in a real request auth()->user() is freshly loaded.
        $landlord->refresh();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        // Landlord: quality (5+4)/2 = 4.5 and communication (5+2)/2 = 3.5 → 4.0.
        // Buildings: average (5.0 + 4.0)/2 = 4.5; best building scores 5.
        // Room titles must never appear: per-room scores would de-anonymise
        // reviews, so the widget aggregates at building level only.
        Livewire::test(ScoreOverview::class)
            ->assertSee('Jouw verhuurderscore')
            ->assertSee('4,0 / 5')
            ->assertSee('4,5 / 5')
            ->assertSee('5 / 5')
            ->assertSee('Gemiddelde gebouwscore')
            ->assertSee('Residentie Park')
            ->assertDontSee('Zolderstudio')
            ->assertDontSee('kotscore');
    }

    public function test_best_building_tiebreak_prefers_the_most_reviewed_building(): void
    {
        $landlord = $this->landlord();
        $onceReviewed = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Eén beoordeling']);
        $twiceReviewed = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Twee beoordelingen']);
        $onceRoom = Room::factory()->for($onceReviewed)->create();
        $twiceRoom = Room::factory()->for($twiceReviewed)->create();

        // All 4/4/4 → both buildings score exactly 4.0.
        $scores = ['score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 4];
        RoomReview::factory()->forRoom($onceRoom)->create($scores);
        RoomReview::factory()->forRoom($twiceRoom)->create($scores);
        RoomReview::factory()->forRoom($twiceRoom)->create($scores);

        $landlord->refresh();
        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ScoreOverview::class)
            ->assertSee('Twee beoordelingen')
            ->assertDontSee('Eén beoordeling');
    }

    public function test_widget_shows_empty_state_without_reviews(): void
    {
        $landlord = $this->landlord();
        Building::factory()->create(['landlord_id' => $landlord->id]);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ScoreOverview::class)
            ->assertSee('—')
            ->assertSee('Nog geen beoordelingen ontvangen')
            ->assertSee('Nog geen beoordeelde gebouwen');
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

        // >= 4.0 gets a green badge; below 4.0 the brand orange (warning).
        Livewire::test(BuildingsOverviewTable::class)
            ->assertSee('Kotscore')
            ->assertSee('4,0 (1)')
            ->assertSee('2,0 (1)')
            ->assertSee('bg-success-100')
            ->assertSee('bg-warning-100');
    }
}
