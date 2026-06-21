<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Models\Building;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_allows_owner_and_recipient_denies_stranger(): void
    {
        $landlord = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $tenant = User::factory()->create();
        $room = Room::factory()->create(['building_id' => $building->id]);
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);
        $stranger = User::factory()->create();

        $doc = $landlord->documents()->create([
            'name' => 'Voor student', 'type' => 'other',
            'visibility' => DocumentVisibility::User, 'shared_with_user_id' => $tenant->id,
        ]);

        $this->assertTrue($landlord->can('view', $doc));  // owner
        $this->assertTrue($tenant->can('view', $doc));    // recipient
        $this->assertFalse($stranger->can('view', $doc)); // stranger
    }
}
