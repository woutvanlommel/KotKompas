<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Filament\Dashboard\Pages\Documents;
use App\Models\Building;
use App\Models\Document;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
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

    public function test_toggle_visibility_flips_private_and_landlord(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $doc = $tenant->documents()->create([
            'name' => 'X', 'type' => 'other', 'visibility' => DocumentVisibility::Private,
        ]);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->call('toggleVisibility', $doc->id);
        $this->assertSame(DocumentVisibility::Landlord, $doc->refresh()->visibility);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->call('toggleVisibility', $doc->id);
        $this->assertSame(DocumentVisibility::Private, $doc->refresh()->visibility);
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
