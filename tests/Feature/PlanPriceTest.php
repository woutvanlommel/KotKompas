<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Pages\Subscription;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanPriceTest extends TestCase
{
    use RefreshDatabase;

    private function plan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'slug' => 'pro',
            'name' => 'Pro',
            'description' => 'Voor de actieve verhuurder',
            'features' => ['1 uitgelichte kamer'],
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function landlord(): User
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        return $landlord;
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_plan_stores_and_casts_monthly_price(): void
    {
        $plan = $this->plan(['monthly_price' => 9.99]);

        $this->assertSame(9.99, (float) $plan->fresh()->monthly_price);
        $this->assertDatabaseHas('plans', ['slug' => 'pro', 'monthly_price' => 9.99]);
    }

    public function test_subscription_page_shows_monthly_price_when_set(): void
    {
        $this->plan(['slug' => 'pro', 'name' => 'Pro', 'monthly_price' => 9.99]);

        $this->actingAs($this->landlord());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(Subscription::class)
            ->assertSee('9,99')
            ->assertSee('/ maand');
    }

    public function test_subscription_page_falls_back_when_monthly_price_null(): void
    {
        $this->plan(['slug' => 'pro', 'name' => 'Pro', 'monthly_price' => null]);

        $this->actingAs($this->landlord());
        Filament::setCurrentPanel('dashboard');

        Livewire::test(Subscription::class)
            ->assertDontSee('/ maand')
            ->assertSee('je ziet het bedrag bij het afrekenen');
    }

    public function test_admin_can_update_plan_monthly_price(): void
    {
        $plan = $this->plan(['monthly_price' => null]);

        $this->actingAs($this->admin());
        Filament::setCurrentPanel('admin');

        Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
            ->fillForm(['monthly_price' => 12.5])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(12.5, (float) $plan->fresh()->monthly_price);
    }
}
