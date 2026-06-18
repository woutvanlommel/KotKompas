<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Resources\Buildings\Pages\ListBuildings;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuildingsTableTest extends TestCase
{
    use RefreshDatabase;

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    public function test_the_buildings_table_renders_portfolio_columns_without_errors(): void
    {
        $landlord = $this->landlord();

        // Building with a mix of available + rented rooms and a score.
        $occupied = Building::factory()->create([
            'landlord_id' => $landlord->id,
            'name' => 'Studentenkot Centrum',
            'score' => 4.2,
            'reviews_count' => 6,
        ]);
        Room::factory()->for($occupied)->create(['status' => 'available']);
        Room::factory()->for($occupied)->create(['status' => 'rented']);

        // Building with no rooms and no reviews (null score) — edge case.
        Building::factory()->create([
            'landlord_id' => $landlord->id,
            'name' => 'Leeg Gebouw',
            'score' => null,
            'reviews_count' => 0,
        ]);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ListBuildings::class)
            ->assertOk()
            // Both buildings render (incl. the no-rooms / no-score edge cases)
            // without errors — the portfolio columns resolve cleanly.
            ->assertSee('Studentenkot Centrum')
            ->assertSee('Leeg Gebouw')
            ->assertSee('Geen kamers')
            ->assertSee('Nog geen score');
    }
}
