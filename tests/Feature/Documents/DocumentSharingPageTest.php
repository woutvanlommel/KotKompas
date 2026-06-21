<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Filament\Dashboard\Pages\Documents;
use App\Models\Building;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentSharingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('dashboard');
    }

    public function test_edit_document_action_updates_name_type_and_visibility(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $doc = $tenant->documents()->create([
            'name' => 'Oud', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('editDocument', data: [
                'name' => 'Nieuw',
                'type' => 'school',
                'visibility' => DocumentVisibility::Landlord->value,
            ], arguments: ['documentId' => $doc->id]);

        $doc->refresh();
        $this->assertSame('Nieuw', $doc->name);
        $this->assertSame('school', $doc->type);
        $this->assertSame(DocumentVisibility::Landlord, $doc->visibility);
    }

    public function test_edit_document_action_can_set_building_scope_for_verhuurder(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $doc = $landlord->documents()->create([
            'name' => 'Huisregels', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        Livewire::actingAs($landlord)->test(Documents::class)
            ->callAction('editDocument', data: [
                'name' => 'Huisregels',
                'type' => 'other',
                'visibility' => DocumentVisibility::Building->value,
                'building_id' => $building->id,
            ], arguments: ['documentId' => $doc->id]);

        $doc->refresh();
        $this->assertSame(DocumentVisibility::Building, $doc->visibility);
        $this->assertSame($building->id, $doc->building_id);
    }

    public function test_edit_document_action_cannot_target_another_users_document(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $other = User::factory()->create();
        $other->assignRole('huurder');
        $doc = $other->documents()->create([
            'name' => 'Niet van mij', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        // Scoping to auth()->user()->documents() means the document is not found
        // for this user; the edit must not go through.
        try {
            Livewire::actingAs($tenant)->test(Documents::class)
                ->callAction('editDocument', data: [
                    'name' => 'Gekaapt',
                    'type' => 'other',
                    'visibility' => DocumentVisibility::Private->value,
                ], arguments: ['documentId' => $doc->id]);
        } catch (ModelNotFoundException) {
            // expected
        }

        $this->assertSame('Niet van mij', $doc->refresh()->name);
    }

    public function test_shared_with_me_returns_documents_shared_to_viewer(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['building_id' => $building->id]);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);

        $shared = $landlord->documents()->create([
            'name' => 'Huisregels', 'type' => 'other',
            'visibility' => DocumentVisibility::Building, 'building_id' => $building->id,
        ]);

        $component = Livewire::actingAs($tenant)->test(Documents::class);
        $ids = $component->instance()->getSharedWithMe()->pluck('id');
        $this->assertTrue($ids->contains($shared->id));

        $component->assertSuccessful();
    }
}
