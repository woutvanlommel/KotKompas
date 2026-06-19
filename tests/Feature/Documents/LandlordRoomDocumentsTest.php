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

class LandlordRoomDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_tenant_documents_returns_landlord_shared_docs(): void
    {
        $landlord = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['building_id' => $building->id]);
        $tenant = User::factory()->create();
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);

        $shared = $tenant->documents()->create([
            'name' => 'Inschrijving', 'type' => 'school',
            'visibility' => DocumentVisibility::Landlord, 'rental_period_id' => $period->id,
        ]);
        $private = $tenant->documents()->create([
            'name' => 'Privé', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        $page = new class($room)
        {
            use \App\Filament\Dashboard\Resources\Rooms\Concerns\HasDocumentActions;

            public function __construct(public $record) {}
        };

        $this->actingAs($landlord);
        $ids = $page->getTenantDocuments()->pluck('id');

        $this->assertTrue($ids->contains($shared->id));
        $this->assertFalse($ids->contains($private->id));
    }
}
