<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Models\Building;
use App\Models\Document;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentVisibilityScopeTest extends TestCase
{
    use RefreshDatabase;

    private function rentingTenant(User $landlord, Building $building): User
    {
        $tenant = User::factory()->create();
        $room = Room::factory()->create(['building_id' => $building->id]);
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);

        return $tenant;
    }

    public function test_visible_to_matrix(): void
    {
        $landlord = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $tenant = $this->rentingTenant($landlord, $building);
        $stranger = User::factory()->create();

        // building-scoped doc owned by landlord
        $buildingDoc = $landlord->documents()->create([
            'name' => 'Huisregels', 'type' => 'other',
            'visibility' => DocumentVisibility::Building, 'building_id' => $building->id,
        ]);
        // user-scoped doc to the tenant
        $userDoc = $landlord->documents()->create([
            'name' => 'Persoonlijk', 'type' => 'other',
            'visibility' => DocumentVisibility::User, 'shared_with_user_id' => $tenant->id,
        ]);
        // huurder shares with landlord
        $landlordDoc = $tenant->documents()->create([
            'name' => 'Inschrijving', 'type' => 'other',
            'visibility' => DocumentVisibility::Landlord,
        ]);
        // private doc owned by stranger
        $privateDoc = $stranger->documents()->create([
            'name' => 'Geheim', 'type' => 'other',
            'visibility' => DocumentVisibility::Private,
        ]);

        $tenantVisible = Document::visibleTo($tenant)->pluck('id');
        $this->assertTrue($tenantVisible->contains($buildingDoc->id));
        $this->assertTrue($tenantVisible->contains($userDoc->id));
        $this->assertTrue($tenantVisible->contains($landlordDoc->id)); // owner
        $this->assertFalse($tenantVisible->contains($privateDoc->id));

        $landlordVisible = Document::visibleTo($landlord)->pluck('id');
        $this->assertTrue($landlordVisible->contains($landlordDoc->id)); // shared up
        $this->assertFalse($landlordVisible->contains($privateDoc->id));

        $strangerVisible = Document::visibleTo($stranger)->pluck('id');
        $this->assertFalse($strangerVisible->contains($buildingDoc->id));
        $this->assertFalse($strangerVisible->contains($userDoc->id));
        $this->assertTrue($strangerVisible->contains($privateDoc->id)); // owner
    }

    public function test_shared_with_excludes_own_documents(): void
    {
        $landlord = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $tenant = $this->rentingTenant($landlord, $building);

        $landlord->documents()->create([
            'name' => 'Huisregels', 'type' => 'other',
            'visibility' => DocumentVisibility::Building, 'building_id' => $building->id,
        ]);
        $ownPrivate = $tenant->documents()->create([
            'name' => 'Eigen', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        $shared = Document::sharedWith($tenant)->pluck('id');
        $this->assertCount(1, $shared);
        $this->assertFalse($shared->contains($ownPrivate->id));
    }
}
