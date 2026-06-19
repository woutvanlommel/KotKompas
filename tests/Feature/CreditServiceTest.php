<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CreditService
    {
        return app(CreditService::class);
    }

    public function test_balance_is_the_sum_of_all_ledger_rows(): void
    {
        $user = User::factory()->create();
        $service = $this->service();

        $service->add($user, 50, 'pack_purchase', 'cs_a');
        $service->add($user, 45, 'pack_purchase', 'cs_b');
        $service->spend($user, 20, 'unlock_landlord:1');

        $this->assertSame(75, $service->balance($user));
    }

    public function test_balance_is_zero_without_transactions(): void
    {
        $this->assertSame(0, $this->service()->balance(User::factory()->create()));
    }

    public function test_add_stores_amount_paid_snapshot(): void
    {
        $user = User::factory()->create();

        $this->service()->add($user, 50, 'pack_purchase', 'cs_paid', amountPaid: 5500);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'amount' => 50,
            'amount_paid' => 5500,
            'reason' => 'pack_purchase',
            'stripe_session_id' => 'cs_paid',
        ]);
    }

    public function test_add_is_idempotent_on_stripe_session_id(): void
    {
        $user = User::factory()->create();
        $service = $this->service();

        $first = $service->add($user, 50, 'pack_purchase', 'cs_dup', amountPaid: 5500);
        $second = $service->add($user, 50, 'pack_purchase', 'cs_dup', amountPaid: 5500);

        $this->assertNotNull($first);
        $this->assertNull($second); // tweede webhook met dezelfde sessie wordt genegeerd
        $this->assertSame(50, $service->balance($user));
        $this->assertSame(1, CreditTransaction::where('user_id', $user->id)->count());
    }

    public function test_add_rejects_non_positive_amounts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service()->add(User::factory()->create(), 0, 'pack_purchase');
    }

    public function test_spend_deducts_a_negative_ledger_row(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $service->add($user, 10, 'seed', 'cs_seed');

        $tx = $service->spend($user, 4, 'unlock_landlord:9');

        $this->assertSame(-4, $tx->amount);
        $this->assertSame('unlock_landlord:9', $tx->reason);
        $this->assertSame(6, $service->balance($user));
    }

    public function test_spend_throws_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $this->service()->add($user, 1, 'seed', 'cs_seed');

        $this->expectException(InsufficientCreditsException::class);

        $this->service()->spend($user, 2, 'unlock_landlord:9');
    }

    public function test_spend_does_not_change_balance_when_it_throws(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $service->add($user, 1, 'seed', 'cs_seed');

        try {
            $service->spend($user, 5, 'unlock_landlord:9');
        } catch (InsufficientCreditsException) {
            // verwacht
        }

        $this->assertSame(1, $service->balance($user));
    }

    public function test_credit_transaction_labels_are_human_readable(): void
    {
        $purchase = new CreditTransaction(['reason' => 'pack_purchase']);
        $unlock = new CreditTransaction(['reason' => 'unlock_landlord:42']);
        $other = new CreditTransaction(['reason' => 'some_reason']);

        $this->assertSame('Credits gekocht', $purchase->label());
        $this->assertSame('Verhuurder ontgrendeld', $unlock->label());
        $this->assertSame('Some reason', $other->label());
    }
}
