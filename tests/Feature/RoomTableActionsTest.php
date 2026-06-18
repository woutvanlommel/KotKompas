<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Resources\Rooms\Pages\ListRooms;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoomTableActionsTest extends TestCase
{
    use RefreshDatabase;

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    public function test_landlord_can_change_room_status_from_the_table(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ListRooms::class)
            ->callAction(TestAction::make('updateStatus')->table($room), data: ['status' => 'maintenance'])
            ->assertNotified('Status bijgewerkt');

        $this->assertSame('maintenance', $room->fresh()->status);
    }

    public function test_landlord_can_link_a_tenant_from_the_table(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ListRooms::class)
            ->callAction(TestAction::make('linkTenant')->table($room), data: ['tenant_id' => $tenant->id])
            ->assertNotified('Huurder gekoppeld');

        $room->refresh();
        $this->assertSame($tenant->id, $room->tenant_id);
        $this->assertSame('rented', $room->status);
        $this->assertDatabaseHas('rental_periods', ['room_id' => $room->id]);
    }

    public function test_a_non_huurder_cannot_be_linked_as_tenant(): void
    {
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        // Another verhuurder — the tenant_id is client-controlled, so the role
        // gate must reject linking a non-huurder server-side.
        $otherLandlord = User::factory()->create();
        $otherLandlord->assignRole('verhuurder');

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        try {
            Livewire::test(ListRooms::class)
                ->callAction(TestAction::make('linkTenant')->table($room), data: ['tenant_id' => $otherLandlord->id]);
        } catch (HttpException $e) {
            // Server-side role gate (abort_unless) is also acceptable.
        }

        // Whatever the mechanism, the non-huurder must NOT be linked.
        $room->refresh();
        $this->assertNull($room->tenant_id);
        $this->assertSame('available', $room->status);
        $this->assertDatabaseMissing('rental_periods', ['room_id' => $room->id]);
    }
}
