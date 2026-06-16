<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use App\Services\FeaturedListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Events\WebhookHandled;
use Tests\TestCase;

class FeaturedListingTest extends TestCase
{
    use RefreshDatabase;

    private function landlordWithPlan(string $slug): User
    {
        config(["subscriptions.plans.{$slug}" => "price_test_{$slug}"]);

        $landlord = User::factory()->create();

        DB::table('subscriptions')->insert([
            'user_id' => $landlord->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.$landlord->id,
            'stripe_status' => 'active',
            'stripe_price' => "price_test_{$slug}",
            'quantity' => 1,
            'renews_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $landlord->refresh();
    }

    private function roomFor(User $landlord, array $attrs = []): Room
    {
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);

        return Room::factory()->create(array_merge([
            'building_id' => $building->id,
            'status' => 'available',
        ], $attrs));
    }

    private function service(): FeaturedListingService
    {
        return app(FeaturedListingService::class);
    }

    // -- Model -------------------------------------------------------------

    public function test_featured_requires_both_the_flag_and_an_open_window(): void
    {
        $live = Room::factory()->create(['is_featured' => true, 'featured_until' => now()->addWeek()]);
        $expired = Room::factory()->create(['is_featured' => true, 'featured_until' => now()->subDay()]);
        $unflagged = Room::factory()->create(['is_featured' => false, 'featured_until' => now()->addWeek()]);

        $this->assertTrue($live->isFeatured());
        $this->assertFalse($expired->isFeatured());
        $this->assertFalse($unflagged->isFeatured());

        $this->assertEquals([$live->id], Room::featured()->pluck('id')->all());
    }

    // -- Slot enforcement --------------------------------------------------

    public function test_feature_respects_plan_slot_limits(): void
    {
        $landlord = $this->landlordWithPlan('pro'); // 1 slot

        $first = $this->roomFor($landlord);
        $second = $this->roomFor($landlord);

        $this->assertTrue($this->service()->feature($first));
        $this->assertFalse($this->service()->feature($second));

        $this->assertTrue($first->fresh()->isFeatured());
        $this->assertFalse($second->fresh()->isFeatured());
    }

    public function test_feature_is_denied_without_a_subscription(): void
    {
        $landlord = User::factory()->create();
        $room = $this->roomFor($landlord);

        $this->assertFalse($this->service()->feature($room));
        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_unfeature_clears_the_room(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        $room = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->addMonth()]);

        $this->service()->unfeature($room);

        $this->assertFalse($room->fresh()->isFeatured());
        $this->assertNull($room->fresh()->featured_until);
    }

    // -- Subscription sync -------------------------------------------------

    public function test_renewal_re_extends_a_window_that_lapsed_at_the_renewal_moment(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        // Intent set, but the window just elapsed — the renewal race scenario.
        $room = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->subMinutes(2)]);

        $this->assertFalse($room->isFeatured());

        $this->service()->syncForLandlord($landlord->fresh(), now()->addMonth());

        $this->assertTrue($room->fresh()->isFeatured());
    }

    public function test_downgrade_prunes_lowest_scored_rooms_first(): void
    {
        $landlord = $this->landlordWithPlan('pro'); // 1 slot
        $window = now()->addMonth();

        $high = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => $window, 'score_bayesian' => 4.8]);
        $mid = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => $window, 'score_bayesian' => 3.2]);
        $low = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => $window, 'score_bayesian' => 1.0]);

        $this->service()->syncForLandlord($landlord->fresh(), $window);

        $this->assertTrue($high->fresh()->isFeatured());
        $this->assertFalse($mid->fresh()->isFeatured());
        $this->assertFalse($low->fresh()->isFeatured());
    }

    public function test_unfeature_all_drops_every_featured_room(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        $a = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->addMonth()]);
        $b = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->addMonth()]);

        $this->service()->unfeatureAll($landlord);

        $this->assertFalse($a->fresh()->isFeatured());
        $this->assertFalse($b->fresh()->isFeatured());
    }

    // -- Webhook wiring ----------------------------------------------------

    public function test_subscription_deleted_webhook_unfeatures_rooms(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        $room = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->addMonth()]);

        event(new WebhookHandled([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => 'sub_'.$landlord->id]],
        ]));

        $this->assertFalse($room->fresh()->isFeatured());
    }

    public function test_renewal_webhook_extends_the_featured_window(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        $room = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => now()->subMinutes(2)]);

        event(new WebhookHandled([
            'type' => 'customer.subscription.updated',
            'data' => ['object' => [
                'id' => 'sub_'.$landlord->id,
                'items' => ['data' => [[
                    'current_period_end' => now()->addMonths(2)->timestamp,
                    'price' => ['id' => 'price_test_premium'],
                ]]],
            ]],
        ]));

        $this->assertTrue($room->fresh()->isFeatured());
    }

    // -- Public ordering ---------------------------------------------------

    public function test_featured_rooms_rank_first_ordered_by_score(): void
    {
        $landlord = $this->landlordWithPlan('premium');
        $window = now()->addMonth();

        $featuredHigh = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => $window, 'score_bayesian' => 4.5]);
        $featuredLow = $this->roomFor($landlord, ['is_featured' => true, 'featured_until' => $window, 'score_bayesian' => 2.0]);
        $plainA = $this->roomFor($landlord, ['is_featured' => false]);
        $plainB = $this->roomFor($landlord, ['is_featured' => false]);

        $ids = $this->get(route('rooms.index'))
            ->assertOk()
            ->viewData('rooms')
            ->pluck('id')
            ->all();

        // Featured first, best score leading; both plain rooms trail behind.
        $this->assertSame($featuredHigh->id, $ids[0]);
        $this->assertSame($featuredLow->id, $ids[1]);
        $this->assertLessThan(array_search($plainA->id, $ids, true), array_search($featuredLow->id, $ids, true));
        $this->assertLessThan(array_search($plainB->id, $ids, true), array_search($featuredLow->id, $ids, true));
    }
}
