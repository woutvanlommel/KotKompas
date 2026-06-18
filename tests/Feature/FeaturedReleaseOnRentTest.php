<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Support\LinkRoomTenant;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedReleaseOnRentTest extends TestCase
{
    use RefreshDatabase;

    private function featuredRoom(User $landlord): Room
    {
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);

        return Room::factory()->for($building)->create([
            'status' => 'available',
            'is_featured' => true,
            'featured_until' => now()->addDays(30),
        ]);
    }

    public function test_renting_a_featured_room_releases_the_highlight_and_slot(): void
    {
        $landlord = User::factory()->create();
        $room = $this->featuredRoom($landlord);

        $this->assertSame(1, $landlord->featuredSlotsUsed());

        $room->update(['status' => 'rented']);

        $room->refresh();
        $this->assertFalse($room->is_featured);
        $this->assertNull($room->featured_until);
        $this->assertSame(0, $landlord->fresh()->featuredSlotsUsed());
    }

    public function test_maintenance_or_archived_also_releases_the_highlight(): void
    {
        $landlord = User::factory()->create();

        foreach (['maintenance', 'archived'] as $status) {
            $room = $this->featuredRoom($landlord);
            $room->update(['status' => $status]);

            $this->assertFalse($room->fresh()->is_featured, "status {$status} should release highlight");
        }
    }

    public function test_an_available_featured_room_keeps_its_highlight(): void
    {
        $landlord = User::factory()->create();
        $room = $this->featuredRoom($landlord);

        // A non-status change must not touch the highlight.
        $room->update(['price_per_month' => 555]);

        $this->assertTrue($room->fresh()->is_featured);
        $this->assertNotNull($room->fresh()->featured_until);
    }

    public function test_linking_a_tenant_releases_the_highlight(): void
    {
        $this->seed(RoleSeeder::class);

        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $room = $this->featuredRoom($landlord);

        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        // LinkRoomTenant flips status to "rented" → highlight must release.
        LinkRoomTenant::handle($room, $tenant->id);

        $room->refresh();
        $this->assertSame('rented', $room->status);
        $this->assertFalse($room->is_featured);
        $this->assertNull($room->featured_until);
    }
}
