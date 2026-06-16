<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Resources\Buildings\Pages\ViewBuilding;
use App\Filament\Dashboard\Resources\Rooms\Pages\ListRooms;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureRoomActionTest extends TestCase
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

    public function test_feature_without_slots_nudges_to_subscription(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ListRooms::class)
            ->callAction(TestAction::make('feature')->table($room))
            ->assertNotified('Geen uitlicht-slots beschikbaar');

        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_feature_with_a_slot_marks_the_room_featured(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ListRooms::class)
            ->callAction(TestAction::make('feature')->table($room))
            ->assertNotified('Kot uitgelicht');

        $this->assertTrue($room->fresh()->isFeatured());
    }

    // -- Building view: per-room toggle from the room cards -----------------

    public function test_building_view_can_feature_a_room(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewBuilding::class, ['record' => $building->getRouteKey()])
            ->callAction('featureRoom', arguments: ['room' => $room->id])
            ->assertNotified('Kot uitgelicht');

        $this->assertTrue($room->fresh()->isFeatured());
    }

    public function test_building_view_feature_without_slots_nudges(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewBuilding::class, ['record' => $building->getRouteKey()])
            ->callAction('featureRoom', arguments: ['room' => $room->id])
            ->assertNotified('Geen uitlicht-slots beschikbaar');

        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_building_view_cannot_feature_a_room_from_another_building(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $foreignRoom = Room::factory()->create(); // belongs to some other building

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ViewBuilding::class, ['record' => $building->getRouteKey()])
            ->callAction('featureRoom', arguments: ['room' => $foreignRoom->id]);
    }
}
