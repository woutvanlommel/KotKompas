<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Widgets\SubscriptionOverview;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    private function subscribe(User $user, string $slug): void
    {
        config(["subscriptions.plans.{$slug}" => "price_test_{$slug}"]);

        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.$user->id,
            'stripe_status' => 'active',
            'stripe_price' => "price_test_{$slug}",
            'quantity' => 1,
            'renews_at' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_widget_shows_current_plan_and_slot_usage(): void
    {
        $landlord = $this->landlord();
        $this->subscribe($landlord, 'premium'); // 3 slots

        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        Room::factory()->for($building)->create(['is_featured' => true, 'featured_until' => now()->addMonth()]);

        $this->actingAs($landlord->refresh());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(SubscriptionOverview::class)
            ->assertSee('Premium')
            ->assertSee('1 / 3')
            ->assertSee('Beheer abonnement');
    }

    public function test_widget_prompts_to_subscribe_without_a_plan(): void
    {
        $landlord = $this->landlord();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(SubscriptionOverview::class)
            ->assertSee('geen actief abonnement')
            ->assertSee('Kies een abonnement');
    }

    public function test_widget_is_hidden_for_non_landlords(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $this->actingAs($tenant);

        $this->assertFalse(SubscriptionOverview::canView());
    }
}
