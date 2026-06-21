<?php

namespace Tests\Feature\Documents;

use App\Models\Building;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserVisibilityHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_rental_building_ids_and_tenant_user_ids(): void
    {
        $landlord = User::factory()->create();
        $tenant = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['building_id' => $building->id]);

        $active = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $active->tenants()->attach($tenant->id, ['is_primary' => true]);

        // Expired period must be ignored.
        $otherTenant = User::factory()->create();
        $expired = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subYear(), 'end_date' => now()->subMonth()]);
        $expired->tenants()->attach($otherTenant->id, ['is_primary' => true]);

        $this->assertEqualsCanonicalizing([$building->id], $tenant->activeRentalBuildingIds());
        $this->assertSame([], $otherTenant->activeRentalBuildingIds());

        $this->assertEqualsCanonicalizing([$tenant->id], $landlord->activeTenantUserIds());
    }
}
