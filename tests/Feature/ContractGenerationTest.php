<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Pages\Documents;
use App\Filament\Dashboard\Resources\Rooms\Pages\ViewRoom;
use App\Models\Building;
use App\Models\Document;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('dashboard');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function makeScenario(): array
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $building = Building::factory()->create(['landlord_id' => $landlord->id]);

        $room = Room::factory()->for($building)->create([
            'tenant_id' => $tenant->id,
            'status' => 'rented',
            'price_per_month' => 600,
            'deposit_amount' => 1200,
            'room_number' => '12',
        ]);

        $period = RentalPeriod::create([
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => null,
        ]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);

        return compact('landlord', 'tenant', 'building', 'room', 'period');
    }

    private function createContract(Room $room, User $landlord, array $overrides = []): Document
    {
        $start = $overrides['start_date'] ?? now()->toDateString();
        $end = $overrides['end_date'] ?? Carbon::parse($start)->addMonths(10)->toDateString();

        Livewire::actingAs($landlord)
            ->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('createContract', data: array_merge([
                'name' => 'Huurcontract 2025-2026',
                'start_date' => $start,
                'duration_months' => 10,
                'end_date' => $end,
                'special_conditions' => null,
            ], $overrides));

        return Document::where('type', 'contract')->latest()->firstOrFail();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Contract aanmaken
    // ─────────────────────────────────────────────────────────────────────────

    public function test_verhuurder_kan_contract_aanmaken_voor_gekoppelde_huurder(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        $this->assertDatabaseHas('documents', [
            'type' => 'contract',
            'status' => 'draft',
            'rental_period_id' => $period->id,
        ]);

        $this->assertSame($tenant->id, $contract->blocks['partijen']['huurders'][0]['user_id']);
        $this->assertSame($landlord->full_name, $contract->blocks['partijen']['verhuurder']['naam']);
        $this->assertSame(10, $contract->blocks['huurperiode']['duur_maanden']);
        $this->assertSame(600.0, (float) $contract->blocks['financieel']['huurprijs']);
        $this->assertSame(1200.0, (float) $contract->blocks['financieel']['borgsom']);
    }

    public function test_contract_aanmaken_update_start_en_einddatum_van_huurperiode(): void
    {
        ['landlord' => $landlord, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $this->createContract($room, $landlord, [
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'duration_months' => 10,
        ]);

        $period->refresh();
        $this->assertSame('2025-09-01', $period->start_date->toDateString());
        $this->assertSame('2026-06-30', $period->end_date->toDateString());
    }

    public function test_contract_bevat_alle_huurders_inclusief_medehuurder(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $coTenant = User::factory()->create();
        $coTenant->assignRole('huurder');
        $period->tenants()->attach($coTenant->id, ['is_primary' => false]);

        $contract = $this->createContract($room, $landlord);

        $huurders = $contract->blocks['partijen']['huurders'];
        $this->assertCount(2, $huurders);
        $this->assertTrue(collect($huurders)->where('is_primary', true)->pluck('user_id')->contains($tenant->id));
        $this->assertTrue(collect($huurders)->where('is_primary', false)->pluck('user_id')->contains($coTenant->id));
    }

    public function test_contract_aanmaken_zonder_actieve_huurder_heeft_geen_huurperiode(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        Livewire::actingAs($landlord)
            ->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('createContract', data: [
                'name' => 'Test',
                'start_date' => now()->toDateString(),
                'duration_months' => 10,
                'end_date' => now()->addMonths(10)->toDateString(),
            ]);

        $this->assertNull(Document::where('type', 'contract')->first()?->rental_period_id);
    }

    public function test_bijzondere_voorwaarden_worden_opgeslagen(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord, [
            'special_conditions' => 'Geen huisdieren toegestaan.',
        ]);

        $this->assertSame('Geen huisdieren toegestaan.', $contract->blocks['bijzondere_voorwaarden']);
    }

    public function test_verhuurder_kan_enkel_contract_aanmaken_voor_eigen_kamer(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('verhuurder');
        $other = User::factory()->create();
        $other->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $owner->id]);
        $room = Room::factory()->for($building)->create();

        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($other)->test(ViewRoom::class, ['record' => $room->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Contract ondertekenen
    // ─────────────────────────────────────────────────────────────────────────

    public function test_huurder_kan_eigen_contract_ondertekenen(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)
            ->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $handtekeningen = collect($contract->fresh()->blocks['ondertekening']['handtekeningen']);
        $this->assertTrue($handtekeningen->contains('user_id', $tenant->id));
        $this->assertSame('draft', $contract->fresh()->status); // verhuurder heeft nog niet getekend
    }

    public function test_huurder_kan_contract_van_andere_huurder_niet_ondertekenen(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $stranger = User::factory()->create();
        $stranger->assignRole('huurder');

        $contract = $this->createContract($room, $landlord);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($stranger)
            ->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
    }

    public function test_huurder_kan_niet_twee_keer_ondertekenen(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $countAfterFirst = count($contract->fresh()->blocks['ondertekening']['handtekeningen']);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $this->assertSame($countAfterFirst, count($contract->fresh()->blocks['ondertekening']['handtekeningen']));
    }

    public function test_contract_wordt_signed_wanneer_verhuurder_en_huurder_beide_tekenen(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $this->assertSame('draft', $contract->fresh()->status);

        Livewire::actingAs($landlord)->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $this->assertSame('signed', $contract->fresh()->status);
    }

    public function test_contract_met_medehuurder_pas_signed_als_iedereen_getekend_heeft(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $coTenant = User::factory()->create();
        $coTenant->assignRole('huurder');
        $period->tenants()->attach($coTenant->id, ['is_primary' => false]);

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($landlord)->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        $this->assertSame('draft', $contract->fresh()->status);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        $this->assertSame('draft', $contract->fresh()->status);

        Livewire::actingAs($coTenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        $this->assertSame('signed', $contract->fresh()->status);
    }

    public function test_volgorde_van_ondertekenen_maakt_niet_uit(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        // Verhuurder eerst
        Livewire::actingAs($landlord)->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        $this->assertSame('draft', $contract->fresh()->status);

        // Huurder als laatste
        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        $this->assertSame('signed', $contract->fresh()->status);
    }

    public function test_ondertekening_slaat_tijdstip_op_per_partij(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $sig = collect($contract->fresh()->blocks['ondertekening']['handtekeningen'])
            ->firstWhere('user_id', $tenant->id);

        $this->assertNotNull($sig['signed_at']);
        $this->assertFalse($sig['is_verhuurder'] ?? false);
    }

    public function test_verhuurder_handtekening_heeft_is_verhuurder_vlag(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($landlord)->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $sig = collect($contract->fresh()->blocks['ondertekening']['handtekeningen'])
            ->firstWhere('user_id', $landlord->id);

        $this->assertTrue($sig['is_verhuurder']);
    }

    public function test_al_ondertekend_contract_kan_niet_opnieuw_worden_ondertekend(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
        Livewire::actingAs($landlord)->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);

        $this->assertSame('signed', $contract->fresh()->status);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($tenant)->test(Documents::class)
            ->callAction('signContract', arguments: ['documentId' => $contract->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. PDF bekijken
    // ─────────────────────────────────────────────────────────────────────────

    public function test_huurder_kan_eigen_contract_pdf_bekijken(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        $this->actingAs($tenant)
            ->get(route('contracts.pdf', $contract))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_verhuurder_kan_eigen_contract_pdf_bekijken(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        $this->actingAs($landlord)
            ->get(route('contracts.pdf', $contract))
            ->assertOk();
    }

    public function test_huurder_kan_contract_van_andere_huurder_niet_bekijken(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $stranger = User::factory()->create();
        $stranger->assignRole('huurder');

        $contract = $this->createContract($room, $landlord);

        $this->actingAs($stranger)
            ->get(route('contracts.pdf', $contract))
            ->assertForbidden();
    }

    public function test_verhuurder_kan_contract_van_andere_verhuurder_niet_bekijken(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $other = User::factory()->create();
        $other->assignRole('verhuurder');

        $contract = $this->createContract($room, $landlord);

        $this->actingAs($other)
            ->get(route('contracts.pdf', $contract))
            ->assertForbidden();
    }

    public function test_niet_ingelogde_gebruiker_kan_contract_pdf_niet_bekijken(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        auth()->logout();

        $this->get(route('contracts.pdf', $contract))
            ->assertRedirect();
    }

    public function test_pdf_route_geeft_403_voor_niet_contract_document(): void
    {
        ['tenant' => $tenant] = $this->makeScenario();

        // Document zonder rental_period → huurder mag het sowieso niet zien
        $doc = Document::create([
            'user_id' => $tenant->id,
            'name' => 'Identiteitskaart',
            'type' => 'identity',
            'is_public' => false,
            'status' => 'draft',
            'blocks' => [],
        ]);

        $this->actingAs($tenant)
            ->get(route('contracts.pdf', $doc))
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Contract verwijderen
    // ─────────────────────────────────────────────────────────────────────────

    public function test_verhuurder_kan_eigen_contract_verwijderen(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($landlord)
            ->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('deleteContract', arguments: ['documentId' => $contract->id]);

        $this->assertDatabaseMissing('documents', ['id' => $contract->id]);
    }

    public function test_verhuurder_kan_contract_van_andere_verhuurder_niet_verwijderen(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $other = User::factory()->create();
        $other->assignRole('verhuurder');

        $contract = $this->createContract($room, $landlord);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($other)
            ->test(Documents::class)
            ->callAction('deleteContract', arguments: ['documentId' => $contract->id]);
    }

    public function test_huurder_kan_contract_niet_verwijderen(): void
    {
        ['landlord' => $landlord, 'tenant' => $tenant, 'room' => $room] = $this->makeScenario();

        $contract = $this->createContract($room, $landlord);

        Livewire::actingAs($tenant)
            ->test(Documents::class)
            ->callAction('deleteContract', arguments: ['documentId' => $contract->id]);

        // Contract moet nog steeds bestaan
        $this->assertDatabaseHas('documents', ['id' => $contract->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Documenten pagina — zichtbaarheid
    // ─────────────────────────────────────────────────────────────────────────

    public function test_huurder_ziet_enkel_eigen_contracten(): void
    {
        $dataA = $this->makeScenario();
        $contractA = $this->createContract($dataA['room'], $dataA['landlord'], ['name' => 'Contract Huurder A']);

        $dataB = $this->makeScenario();
        $this->createContract($dataB['room'], $dataB['landlord'], ['name' => 'Contract Huurder B']);

        Livewire::actingAs($dataA['tenant'])
            ->test(Documents::class)
            ->assertSee('Contract Huurder A')
            ->assertDontSee('Contract Huurder B');
    }

    public function test_verhuurder_ziet_alle_contracten_van_eigen_kamers(): void
    {
        ['landlord' => $landlord, 'room' => $room] = $this->makeScenario();

        $this->createContract($room, $landlord, ['name' => 'Mijn Contract']);

        Livewire::actingAs($landlord)
            ->test(Documents::class)
            ->assertSee('Mijn Contract');
    }

    public function test_verhuurder_ziet_geen_contracten_van_andere_verhuurder(): void
    {
        $dataA = $this->makeScenario();
        $this->createContract($dataA['room'], $dataA['landlord'], ['name' => 'Contract Verhuurder A']);

        $landlordB = User::factory()->create();
        $landlordB->assignRole('verhuurder');

        Livewire::actingAs($landlordB)
            ->test(Documents::class)
            ->assertDontSee('Contract Verhuurder A');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Huurperiode datums
    // ─────────────────────────────────────────────────────────────────────────

    public function test_huurperiode_start_en_einddatum_worden_gezet_via_contract(): void
    {
        ['landlord' => $landlord, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $this->createContract($room, $landlord, [
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'duration_months' => 10,
        ]);

        $period->refresh();
        $this->assertSame('2025-09-01', $period->start_date->toDateString());
        $this->assertSame('2026-06-30', $period->end_date->toDateString());
    }

    public function test_huurder_koppelen_vereist_geen_datum_meer(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['status' => 'available']);

        Livewire::actingAs($landlord)
            ->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('linkTenant', data: ['tenant_id' => $tenant->id]);

        $this->assertSame($tenant->id, $room->fresh()->tenant_id);
        $this->assertDatabaseCount('rental_periods', 1);
    }

    public function test_nieuwe_koppeling_sluit_vorige_huurperiode_af(): void
    {
        ['landlord' => $landlord, 'room' => $room, 'period' => $period] = $this->makeScenario();

        $newTenant = User::factory()->create();
        $newTenant->assignRole('huurder');

        Livewire::actingAs($landlord)
            ->test(ViewRoom::class, ['record' => $room->id])
            ->callAction('linkTenant', data: ['tenant_id' => $newTenant->id]);

        $period->refresh();
        $this->assertNotNull($period->end_date);
        $this->assertTrue($period->end_date->isPast());
        $this->assertDatabaseCount('rental_periods', 2);
    }
}
