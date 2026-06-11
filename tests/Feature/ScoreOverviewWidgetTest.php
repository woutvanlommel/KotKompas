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

    public function test_widget_shows_landlord_and_room_scores(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $topRoom = Room::factory()->for($building)->create(['title' => 'Zolderstudio']);
        $otherRoom = Room::factory()->for($building)->create();

        RoomReview::factory()->forRoom($topRoom)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5, 'score_communication' => 5,
        ]);
        RoomReview::factory()->forRoom($otherRoom)->create([
            'score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 2,
        ]);

        // De observer schreef de cache naar de DB via een eigen instance;
        // in een echt request wordt auth()->user() vers geladen.
        $landlord->refresh();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        // Verhuurder: kwaliteit (5+4)/2 = 4,5 en communicatie (5+2)/2 = 3,5 → 4,0.
        // Koten: gemiddeld (5,0 + 4,0)/2 = 4,5; beste kot is de Zolderstudio (5,0).
        Livewire::test(ScoreOverview::class)
            ->assertSee('Jouw verhuurderscore')
            ->assertSee('4,0 / 5')
            ->assertSee('4,5 / 5')
            ->assertSee('5,0 / 5')
            ->assertSee('Zolderstudio');
    }

    public function test_best_room_tiebreak_prefers_the_most_reviewed_room(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $onceReviewed = Room::factory()->for($building)->create(['title' => 'Eén beoordeling']);
        $twiceReviewed = Room::factory()->for($building)->create(['title' => 'Twee beoordelingen']);

        // Allemaal 4/4/4 → beide koten scoren exact 4,0.
        $scores = ['score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 4];
        RoomReview::factory()->forRoom($onceReviewed)->create($scores);
        RoomReview::factory()->forRoom($twiceReviewed)->create($scores);
        RoomReview::factory()->forRoom($twiceReviewed)->create($scores);

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
            ->assertSee('Nog geen beoordeelde koten');
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

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(BuildingsOverviewTable::class)
            ->assertSee('Kotscore')
            ->assertSee('4,0 (1)');
    }
}
