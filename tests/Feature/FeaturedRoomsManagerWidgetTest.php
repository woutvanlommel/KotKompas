<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Widgets\FeaturedRoomsManager;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FeaturedRoomsManagerWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    private function subscribe(User $user, string $slug): void
    {
        config(["subscriptions.plans.{$slug}" => "price_test_{$slug}"]);

        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.$user->id,
            'stripe_status' => 'active',
            'stripe_price' => "price_test_{$slug}",
            'quantity' => 1,
            'renews_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_widget_groups_rooms_per_building_with_slot_usage(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium'); // 3 slots

        $park = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Residentie Park']);
        Room::factory()->for($park)->create(['title' => 'Zolderstudio', 'status' => 'available']);

        $centrum = Building::factory()->create(['landlord_id' => $landlord->id, 'name' => 'Centrum']);
        Room::factory()->for($centrum)->create(['title' => 'Studio Zuid', 'status' => 'available']);

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(FeaturedRoomsManager::class)
            ->assertSee('Uitgelichte koten')
            ->assertSee('Slots in gebruik')
            // Both buildings appear as group headers, each with its room + toggle.
            ->assertSee('Residentie Park')
            ->assertSee('Zolderstudio')
            ->assertSee('Centrum')
            ->assertSee('Studio Zuid')
            ->assertSee('Uitlichten');
    }

    public function test_widget_only_lists_available_rooms(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        Room::factory()->for($building)->create(['title' => 'Vrij Kot', 'status' => 'available']);
        Room::factory()->for($building)->create(['title' => 'Verhuurd Kot', 'status' => 'rented']);

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(FeaturedRoomsManager::class)
            ->assertSee('Vrij Kot')
            ->assertDontSee('Verhuurd Kot');
    }

    public function test_toggle_features_and_unfeatures_a_room(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(FeaturedRoomsManager::class)
            ->call('toggle', $room->id)
            ->assertNotified('Kot uitgelicht');
        $this->assertTrue($room->fresh()->isFeatured());

        Livewire::test(FeaturedRoomsManager::class)
            ->call('toggle', $room->id)
            ->assertNotified('Niet meer uitgelicht');
        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_toggle_without_slots_nudges_to_subscription(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(FeaturedRoomsManager::class)
            ->call('toggle', $room->id)
            ->assertNotified('Geen uitlicht-slots beschikbaar');

        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_toggle_is_scoped_to_the_landlords_own_rooms(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $foreignRoom = Room::factory()->create(['status' => 'available']); // someone else's room

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(FeaturedRoomsManager::class)->call('toggle', $foreignRoom->id);
    }

    public function test_widget_is_hidden_for_non_landlords(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $this->actingAs($tenant);

        $this->assertFalse(FeaturedRoomsManager::canView());
    }
}
