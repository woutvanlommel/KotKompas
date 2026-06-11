<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Resources\Rooms\Pages\ViewRoom;
use App\Models\Building;
use App\Models\ReviewInvitation;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewSurveyTest extends TestCase
{
    use RefreshDatabase;

    public function test_unlinking_a_tenant_issues_a_review_invitation(): void
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $tenant = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['tenant_id' => $tenant->id, 'status' => 'rented']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->callAction('unlinkTenant');

        $room->refresh();
        $this->assertNull($room->tenant_id);
        $this->assertSame('available', $room->status);

        $invitation = ReviewInvitation::query()->sole();
        $this->assertSame($room->id, $invitation->room_id);
        $this->assertSame($tenant->id, $invitation->tenant_id);
        $this->assertSame($landlord->id, $invitation->landlord_id);
        $this->assertTrue($invitation->isOpen());
    }

    public function test_changing_the_tenant_issues_an_invitation_for_the_previous_tenant(): void
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $previousTenant = User::factory()->create();
        $newTenant = User::factory()->create();
        $newTenant->assignRole('huurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['tenant_id' => $previousTenant->id, 'status' => 'rented']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->callAction('linkTenant', data: ['tenant_id' => $newTenant->id]);

        $this->assertSame($newTenant->id, $room->refresh()->tenant_id);

        // De wissel beëindigde de huur van de vorige huurder — die krijgt de enquête.
        $invitation = ReviewInvitation::query()->sole();
        $this->assertSame($previousTenant->id, $invitation->tenant_id);
        $this->assertTrue($invitation->isOpen());
    }

    public function test_an_expired_invitation_can_be_reissued_from_the_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();
        $expired = ReviewInvitation::factory()->forRoom($room)->expired()->create();

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->callAction('reissueReviewInvitation', arguments: ['invitation' => $expired->id]);

        $invitation = ReviewInvitation::query()->sole();
        $this->assertNotSame($expired->token, $invitation->token);
        $this->assertSame($expired->tenant_id, $invitation->tenant_id);
        $this->assertTrue($invitation->isOpen());
    }

    public function test_dashboard_lists_pending_invitations_per_ex_tenant(): void
    {
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create();
        $tenantA = User::factory()->create(['name' => 'Aline Peeters']);
        $tenantB = User::factory()->create(['name' => 'Bram Claes']);
        ReviewInvitation::factory()->forRoom($room)->create(['tenant_id' => $tenantA->id]);
        ReviewInvitation::factory()->forRoom($room)->expired()->create(['tenant_id' => $tenantB->id]);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->assertSee('Beoordelingslinks')
            ->assertSee('Aline Peeters')
            ->assertSee('Bram Claes')
            ->assertSee('Nieuwe link maken');
    }

    public function test_a_landlord_cannot_open_another_landlords_room(): void
    {
        $this->seed(RoleSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('verhuurder');
        $other = User::factory()->create();
        $other->assignRole('verhuurder');
        $building = Building::factory()->create(['landlord_id' => $owner->id]);
        $room = Room::factory()->for($building)->create();

        $this->actingAs($other);
        Filament::setCurrentPanel('dashboard');

        $this->expectException(ModelNotFoundException::class);
        Livewire::test(ViewRoom::class, ['record' => $room->id]);
    }

    public function test_no_invitation_is_issued_when_the_tenant_already_reviewed_the_room(): void
    {
        $room = Room::factory()->create();
        $tenant = User::factory()->create();
        RoomReview::factory()->forRoom($room)->create(['tenant_id' => $tenant->id]);

        $this->assertNull(ReviewInvitation::issueFor($room, $tenant->id));
        $this->assertDatabaseCount('review_invitations', 0);
    }

    public function test_issuing_again_replaces_the_previous_open_invitation(): void
    {
        $room = Room::factory()->create();
        $tenant = User::factory()->create();

        $first = ReviewInvitation::issueFor($room, $tenant->id);
        $second = ReviewInvitation::issueFor($room, $tenant->id);

        $this->assertDatabaseCount('review_invitations', 1);
        $this->assertNotSame($first->token, $second->token);
        $this->get(route('reviews.create', ['invitation' => $first->token]))->assertNotFound();
    }

    public function test_survey_page_shows_the_form_for_a_valid_token(): void
    {
        $room = Room::factory()->create(['title' => 'Zonnig kot aan het park']);
        $invitation = ReviewInvitation::factory()->forRoom($room)->create();

        $this->get(route('reviews.create', $invitation))
            ->assertOk()
            ->assertSee('Hoe was je kot?')
            ->assertSee('Zonnig kot aan het park')
            ->assertSee('Hygiëne')
            ->assertSee('Communicatie verhuurder');
    }

    public function test_survey_page_shows_thanks_for_a_completed_invitation(): void
    {
        $invitation = ReviewInvitation::factory()->completed()->create();

        $this->get(route('reviews.create', $invitation))
            ->assertOk()
            ->assertSee('Bedankt!')
            ->assertDontSee('Verstuur beoordeling');
    }

    public function test_survey_page_shows_expired_state_for_an_expired_invitation(): void
    {
        $invitation = ReviewInvitation::factory()->expired()->create();

        $this->get(route('reviews.create', $invitation))
            ->assertOk()
            ->assertSee('Link verlopen')
            ->assertDontSee('Verstuur beoordeling');
    }

    public function test_unknown_token_is_not_found(): void
    {
        $this->get('/beoordeling/bestaat-niet')->assertNotFound();
    }

    public function test_submitting_the_survey_stores_the_review_and_updates_the_kotscore(): void
    {
        $room = Room::factory()->create();
        $invitation = ReviewInvitation::factory()->forRoom($room)->create();

        $response = $this->post(route('reviews.store', $invitation), [
            'score_hygiene' => 4,
            'score_size' => 4,
            'score_value' => 4,
            'score_communication' => 2,
        ]);

        $response->assertRedirect(route('reviews.create', $invitation));

        $review = RoomReview::query()->sole();
        $this->assertSame($room->id, $review->room_id);
        $this->assertSame($invitation->landlord_id, $review->landlord_id);
        $this->assertSame($invitation->tenant_id, $review->tenant_id);

        $this->assertNotNull($invitation->refresh()->completed_at);
        $this->assertSame(4.0, $room->refresh()->score); // observer-keten draaide

        $this->get(route('reviews.create', $invitation))->assertSee('Bedankt!');
    }

    public function test_submitting_requires_all_four_scores_between_one_and_five(): void
    {
        $invitation = ReviewInvitation::factory()->create();

        $this->post(route('reviews.store', $invitation), [
            'score_hygiene' => 6,
            'score_size' => 0,
        ])->assertSessionHasErrors(['score_hygiene', 'score_size', 'score_value', 'score_communication']);

        $this->assertDatabaseCount('room_reviews', 0);
        $this->assertNull($invitation->refresh()->completed_at);
    }

    public function test_a_completed_invitation_cannot_be_submitted_again(): void
    {
        $invitation = ReviewInvitation::factory()->completed()->create();

        $this->post(route('reviews.store', $invitation), [
            'score_hygiene' => 5,
            'score_size' => 5,
            'score_value' => 5,
            'score_communication' => 5,
        ])->assertRedirect(route('reviews.create', $invitation));

        $this->assertDatabaseCount('room_reviews', 0);
    }

    public function test_an_expired_invitation_cannot_be_submitted(): void
    {
        $invitation = ReviewInvitation::factory()->expired()->create();

        $this->post(route('reviews.store', $invitation), [
            'score_hygiene' => 5,
            'score_size' => 5,
            'score_value' => 5,
            'score_communication' => 5,
        ])->assertRedirect(route('reviews.create', $invitation));

        $this->assertDatabaseCount('room_reviews', 0);
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        $invitation = ReviewInvitation::factory()->create();

        $this->post(route('reviews.store', $invitation), [
            'website' => 'http://spam.example',
            'score_hygiene' => 5,
            'score_size' => 5,
            'score_value' => 5,
            'score_communication' => 5,
        ])->assertRedirect(route('reviews.create', $invitation));

        $this->assertDatabaseCount('room_reviews', 0);
        $this->assertNull($invitation->refresh()->completed_at);
    }
}
