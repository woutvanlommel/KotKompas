<?php

namespace Tests\Feature\Chat;

use App\Livewire\Chat\ChatWindow;
use App\Models\Building;
use App\Models\Conversation;
use App\Models\RentalPeriod;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantMessagingLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config(['chat.tenant_messaging_window_days' => 30]);
        config(['chat.tenant_reply_window_hours' => 48]);
    }

    /**
     * @return array{0: Conversation, 1: User, 2: User, 3: Building}
     *                                                               landlord, tenant, conversation, building
     */
    private function setupConversation(): array
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $building = Building::factory()->create(['landlord_id' => $landlord->id]);

        $conversation = Conversation::create([
            'tenant_id' => $tenant->id,
            'landlord_id' => $landlord->id,
            'building_id' => $building->id,
        ]);

        return [$conversation, $landlord, $tenant, $building];
    }

    private function addPeriod(Building $building, User $tenant, ?string $endDate): void
    {
        $room = Room::factory()->create(['building_id' => $building->id]);
        $period = RentalPeriod::create([
            'room_id' => $room->id,
            'start_date' => now()->subYear(),
            'end_date' => $endDate,
        ]);
        $period->tenants()->attach($tenant->id, ['is_primary' => true]);
    }

    public function test_not_locked_when_period_is_active(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: null);

        $this->assertFalse($conversation->isTenantMessagingLocked());
    }

    public function test_not_locked_when_active_period_exists_alongside_old_ended_period(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(100)->toDateString());
        $this->addPeriod($building, $tenant, endDate: null); // renewed: active again

        $this->assertFalse($conversation->isTenantMessagingLocked());
    }

    public function test_not_locked_within_grace_window(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(10)->toDateString());

        $this->assertFalse($conversation->isTenantMessagingLocked());
    }

    public function test_locked_after_grace_window(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(31)->toDateString());

        $this->assertTrue($conversation->isTenantMessagingLocked());
    }

    public function test_not_locked_when_no_period_in_building(): void
    {
        [$conversation] = $this->setupConversation();

        $this->assertFalse($conversation->isTenantMessagingLocked());
    }

    public function test_locked_tenant_cannot_send_message(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());

        Livewire::actingAs($tenant)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Hallo, mag dit nog?')
            ->call('sendMessage');

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
        ]);
    }

    public function test_landlord_is_never_locked_even_for_ex_tenant(): void
    {
        [$conversation, $landlord, $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(200)->toDateString());

        Livewire::actingAs($landlord)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Over je waarborg...')
            ->call('sendMessage');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $landlord->id,
        ]);
    }

    public function test_tenant_within_grace_can_still_send(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(5)->toDateString());

        Livewire::actingAs($tenant)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Nog een vraag over de sleutels')
            ->call('sendMessage');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
        ]);
    }

    public function test_not_locked_when_reply_window_is_active(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());
        $conversation->update(['tenant_unlocked_until' => now()->addHour()]);

        $this->assertFalse($conversation->isTenantMessagingLocked());
    }

    public function test_locked_again_after_reply_window_expires(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());
        $conversation->update(['tenant_unlocked_until' => now()->subHour()]);

        $this->assertTrue($conversation->isTenantMessagingLocked());
    }

    public function test_landlord_message_grants_reply_window_to_locked_tenant(): void
    {
        [$conversation, $landlord, $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());

        $this->assertNull($conversation->tenant_unlocked_until);

        Livewire::actingAs($landlord)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Ik heb een vraag over je waarborg')
            ->call('sendMessage');

        $conversation->refresh();
        $this->assertNotNull($conversation->tenant_unlocked_until);
        $this->assertTrue($conversation->tenant_unlocked_until->isFuture());
        $this->assertEqualsWithDelta(
            now()->addHours(48)->timestamp,
            $conversation->tenant_unlocked_until->timestamp,
            60
        );
    }

    public function test_landlord_message_does_not_grant_window_when_tenant_within_grace(): void
    {
        [$conversation, $landlord, $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(10)->toDateString());

        Livewire::actingAs($landlord)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Alles goed?')
            ->call('sendMessage');

        $this->assertNull($conversation->refresh()->tenant_unlocked_until);
    }

    public function test_tenant_can_reply_during_reply_window(): void
    {
        [$conversation, , $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());
        $conversation->update(['tenant_unlocked_until' => now()->addDay()]);

        Livewire::actingAs($tenant)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Bedankt, hier is mijn antwoord')
            ->call('sendMessage');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $tenant->id,
        ]);
    }

    public function test_each_landlord_message_extends_the_reply_window(): void
    {
        [$conversation, $landlord, $tenant, $building] = $this->setupConversation();
        $this->addPeriod($building, $tenant, endDate: now()->subDays(60)->toDateString());

        Livewire::actingAs($landlord)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Eerste bericht')
            ->call('sendMessage');
        $first = $conversation->refresh()->tenant_unlocked_until;

        $this->travel(2)->hours();

        Livewire::actingAs($landlord)
            ->test(ChatWindow::class, ['conversationId' => $conversation->id])
            ->set('newMessage', 'Tweede bericht')
            ->call('sendMessage');
        $second = $conversation->refresh()->tenant_unlocked_until;

        $this->assertTrue($second->greaterThan($first));
    }
}
