<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomContactTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Room} landlord + a room they own */
    private function landlordWithRoom(): array
    {
        $this->seed(RoleSeeder::class);

        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');

        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();

        return [$landlord, $room];
    }

    private function huurder(): User
    {
        $huurder = User::factory()->create();
        $huurder->assignRole('huurder');

        return $huurder;
    }

    public function test_huurder_can_message_the_landlord_about_a_room(): void
    {
        [$landlord, $room] = $this->landlordWithRoom();
        $huurder = $this->huurder();

        $this->actingAs($huurder)
            ->post(route('rooms.contact', $room), ['body' => 'Is dit kot nog vrij vanaf september?'])
            ->assertRedirect(route('rooms.show', $room))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('conversations', [
            'tenant_id' => $huurder->id,
            'landlord_id' => $landlord->id,
            'building_id' => $room->building_id,
        ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $huurder->id,
            'body' => 'Is dit kot nog vrij vanaf september?',
        ]);
    }

    public function test_a_second_message_reuses_the_same_conversation(): void
    {
        [, $room] = $this->landlordWithRoom();
        $huurder = $this->huurder();

        $this->actingAs($huurder)->post(route('rooms.contact', $room), ['body' => 'Eerste vraag']);
        $this->actingAs($huurder)->post(route('rooms.contact', $room), ['body' => 'Tweede vraag']);

        $this->assertSame(1, Conversation::where('tenant_id', $huurder->id)->count());
        $this->assertSame(2, Message::where('sender_id', $huurder->id)->count());
    }

    public function test_a_landlord_cannot_message_their_own_listing(): void
    {
        [$landlord, $room] = $this->landlordWithRoom();

        $this->actingAs($landlord)
            ->post(route('rooms.contact', $room), ['body' => 'Test'])
            ->assertForbidden();

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_a_body_is_required(): void
    {
        [, $room] = $this->landlordWithRoom();
        $huurder = $this->huurder();

        $this->actingAs($huurder)
            ->post(route('rooms.contact', $room), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_a_guest_is_redirected_to_the_dashboard_login(): void
    {
        [, $room] = $this->landlordWithRoom();

        $this->post(route('rooms.contact', $room), ['body' => 'Hallo'])
            ->assertRedirect(route('filament.dashboard.auth.login'));

        $this->assertDatabaseCount('messages', 0);
    }
}
