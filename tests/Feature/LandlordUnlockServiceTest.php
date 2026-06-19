<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientCreditsException;
use App\Models\Building;
use App\Models\LandlordUnlock;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use App\Services\CreditService;
use App\Services\LandlordUnlockService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordUnlockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config()->set('credits.unlock_landlord_cost', 2);
    }

    private function service(): LandlordUnlockService
    {
        return app(LandlordUnlockService::class);
    }

    private function huurderWithCredits(int $credits): User
    {
        $huurder = User::factory()->create();
        $huurder->assignRole('huurder');

        if ($credits > 0) {
            app(CreditService::class)->add($huurder, $credits, 'seed', 'cs_'.$huurder->id);
        }

        return $huurder;
    }

    private function landlordWithRoom(): array
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        return [$landlord, $room];
    }

    public function test_cost_comes_from_config(): void
    {
        config()->set('credits.unlock_landlord_cost', 7);

        $this->assertSame(7, $this->service()->cost());
    }

    public function test_unlock_spends_credits_and_creates_an_entitlement_row(): void
    {
        [$landlord] = $this->landlordWithRoom();
        $huurder = $this->huurderWithCredits(10);

        $unlock = $this->service()->unlock($huurder, $landlord);

        $this->assertInstanceOf(LandlordUnlock::class, $unlock);
        $this->assertSame(8, app(CreditService::class)->balance($huurder)); // 10 - 2
        $this->assertDatabaseHas('landlord_unlocks', [
            'tenant_id' => $huurder->id,
            'landlord_id' => $landlord->id,
        ]);
        $this->assertNotNull($unlock->credit_transaction_id);
    }

    public function test_unlock_is_idempotent_and_does_not_charge_twice(): void
    {
        [$landlord] = $this->landlordWithRoom();
        $huurder = $this->huurderWithCredits(10);
        $service = $this->service();

        $service->unlock($huurder, $landlord);
        $service->unlock($huurder, $landlord); // tweede keer

        $this->assertSame(8, app(CreditService::class)->balance($huurder)); // slechts 1x afgeschreven
        $this->assertSame(1, LandlordUnlock::where('tenant_id', $huurder->id)
            ->where('landlord_id', $landlord->id)->count());
    }

    public function test_unlock_does_not_charge_when_tenant_already_rents_from_landlord(): void
    {
        [$landlord, $room] = $this->landlordWithRoom();
        $huurder = $this->huurderWithCredits(10);

        // Bestaande huurrelatie → al toegang, dus geen afschrijving en geen rij.
        $period = RentalPeriod::create(['room_id' => $room->id, 'start_date' => now()->subMonth()]);
        $period->tenants()->attach($huurder->id, ['is_primary' => true]);

        $this->service()->unlock($huurder, $landlord);

        $this->assertSame(10, app(CreditService::class)->balance($huurder)); // niets afgeschreven
        $this->assertDatabaseMissing('landlord_unlocks', [
            'tenant_id' => $huurder->id,
            'landlord_id' => $landlord->id,
        ]);
    }

    public function test_unlock_throws_when_credits_are_insufficient(): void
    {
        [$landlord] = $this->landlordWithRoom();
        $huurder = $this->huurderWithCredits(1); // kost is 2

        $this->expectException(InsufficientCreditsException::class);

        try {
            $this->service()->unlock($huurder, $landlord);
        } finally {
            // geen rij en saldo onaangeroerd na de mislukte transactie
            $this->assertDatabaseMissing('landlord_unlocks', [
                'tenant_id' => $huurder->id,
                'landlord_id' => $landlord->id,
            ]);
            $this->assertSame(1, app(CreditService::class)->balance($huurder));
        }
    }
}
