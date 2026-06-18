<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\LandlordUnlock;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanViewLandlordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function huurder(): User
    {
        $huurder = User::factory()->create();
        $huurder->assignRole('huurder');

        return $huurder;
    }

    private function landlord(): User
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    public function test_without_relation_or_unlock_the_landlord_is_not_viewable(): void
    {
        $this->assertFalse($this->huurder()->canViewLandlord($this->landlord()));
    }

    public function test_an_unlock_row_grants_access_to_all_buildings_of_that_landlord(): void
    {
        $huurder = $this->huurder();
        $landlord = $this->landlord();

        // Twee gebouwen van dezelfde verhuurder.
        $buildingA = Building::factory()->create(['landlord_id' => $landlord->id]);
        $buildingB = Building::factory()->create(['landlord_id' => $landlord->id]);

        // Eén ontgrendeling op verhuurder-niveau.
        LandlordUnlock::create([
            'tenant_id' => $huurder->id,
            'landlord_id' => $landlord->id,
            'unlocked_at' => now(),
        ]);

        // Geldt voor de hele verhuurder → dus voor beide gebouwen.
        $this->assertTrue($huurder->fresh()->canViewLandlord($landlord));
        $this->assertSame(1, LandlordUnlock::where('tenant_id', $huurder->id)->count());
        $this->assertSame($landlord->id, $buildingA->landlord_id);
        $this->assertSame($landlord->id, $buildingB->landlord_id);
    }

    public function test_a_rental_relation_grants_access_without_an_unlock(): void
    {
        $huurder = $this->huurder();
        $landlord = $this->landlord();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($huurder->id, ['is_primary' => true]);

        $this->assertTrue($huurder->canViewLandlord($landlord));
        $this->assertTrue($huurder->hasRentalRelationWith($landlord));
    }

    public function test_a_rental_relation_with_one_landlord_does_not_unlock_another(): void
    {
        $huurder = $this->huurder();
        $landlordA = $this->landlord();
        $landlordB = $this->landlord();

        $building = Building::factory()->create(['landlord_id' => $landlordA->id]);
        $room = Room::factory()->for($building)->create();
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($huurder->id, ['is_primary' => true]);

        $this->assertTrue($huurder->canViewLandlord($landlordA));
        $this->assertFalse($huurder->canViewLandlord($landlordB));
    }
}
