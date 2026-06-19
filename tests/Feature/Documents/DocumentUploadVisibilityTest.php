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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentUploadVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('dashboard');
        Storage::fake('public');
        Storage::fake('local');
        Queue::fake();
    }

    public function test_verhuurder_can_share_with_a_single_student(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['building_id' => $building->id]);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);

        Livewire::actingAs($landlord)
            ->test(Documents::class)
            ->callAction('upload', data: [
                'name' => 'Persoonlijk document',
                'file' => UploadedFile::fake()->create('p.pdf', 50, 'application/pdf'),
                'type' => 'other',
                'visibility' => DocumentVisibility::User->value,
                'building_id' => $building->id,
                'shared_with_user_id' => $tenant->id,
            ]);

        $doc = Document::where('user_id', $landlord->id)->latest()->firstOrFail();
        $this->assertSame(DocumentVisibility::User, $doc->visibility);
        $this->assertSame($tenant->id, $doc->shared_with_user_id);
        $this->assertSame($building->id, $doc->building_id);
    }

    public function test_huurder_can_share_with_landlord(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        Livewire::actingAs($tenant)
            ->test(Documents::class)
            ->callAction('upload', data: [
                'name' => 'Inschrijvingsbewijs',
                'file' => UploadedFile::fake()->create('i.pdf', 50, 'application/pdf'),
                'type' => 'school',
                'visibility' => DocumentVisibility::Landlord->value,
            ]);

        $doc = Document::where('user_id', $tenant->id)->latest()->firstOrFail();
        $this->assertSame(DocumentVisibility::Landlord, $doc->visibility);
    }
}
