<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Widgets\CriteriaBreakdown;
use App\Models\Building;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CriteriaBreakdownWidgetTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Building} */
    private function landlordWithBuilding(): array
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);

        return [$landlord, $building];
    }

    public function test_widget_shows_per_criterion_averages_for_a_landlord(): void
    {
        [$landlord, $building] = $this->landlordWithBuilding();
        $rooms = Room::factory()->count(3)->for($building)->create();

        RoomReview::factory()->forRoom($rooms[0])->create([
            'score_hygiene' => 5, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 2,
        ]);
        RoomReview::factory()->forRoom($rooms[1])->create([
            'score_hygiene' => 3, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 4,
        ]);
        RoomReview::factory()->forRoom($rooms[2])->create([
            'score_hygiene' => 4, 'score_size' => 3, 'score_value' => 4, 'score_communication' => 3,
        ]);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(CriteriaBreakdown::class)
            ->assertSee('Hygiëne')
            ->assertSee('4,0 / 5')          // hygiene (5+3+4)/3 and value (4+4+4)/3
            ->assertSee('Grootte')
            ->assertSee('3,0 / 5')          // size (3+3+3)/3 and communication (2+4+3)/3
            ->assertSee('Prijs-kwaliteit')
            ->assertSee('Communicatie')     // the landlord DOES see communication
            ->assertDontSee('beoordelingen nodig');
    }

    public function test_widget_shows_threshold_message_below_three_reviews(): void
    {
        [$landlord, $building] = $this->landlordWithBuilding();
        $rooms = Room::factory()->count(2)->for($building)->create();
        RoomReview::factory()->forRoom($rooms[0])->create();
        RoomReview::factory()->forRoom($rooms[1])->create();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(CriteriaBreakdown::class)
            ->assertSee('3 beoordelingen')  // explains the anonymity threshold
            ->assertDontSee('Hygiëne');
    }

    public function test_widget_is_hidden_for_non_landlords(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $this->actingAs($tenant);

        $this->assertFalse(CriteriaBreakdown::canView());
    }
}
