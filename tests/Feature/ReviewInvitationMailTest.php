<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Resources\Rooms\Pages\ViewRoom;
use App\Mail\ReviewInvitationMail;
use App\Models\Building;
use App\Models\ReviewInvitation;
use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewInvitationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_unlinking_a_tenant_queues_the_survey_mail_to_the_ex_tenant(): void
    {
        Mail::fake();
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $tenant = User::factory()->create(['email' => 'ex-huurder@example.test']);
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['tenant_id' => $tenant->id, 'status' => 'rented']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->callAction('unlinkTenant');

        Mail::assertQueued(
            ReviewInvitationMail::class,
            fn (ReviewInvitationMail $mail) => $mail->hasTo('ex-huurder@example.test')
        );
    }

    public function test_swapping_the_tenant_mails_the_previous_tenant(): void
    {
        Mail::fake();
        $this->seed(RoleSeeder::class);
        $landlord = User::factory()->create();
        $landlord->assignRole('verhuurder');
        $previousTenant = User::factory()->create(['email' => 'vorige@example.test']);
        $newTenant = User::factory()->create(['email' => 'nieuwe@example.test']);
        $newTenant->assignRole('huurder');
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->for($building)->create(['tenant_id' => $previousTenant->id, 'status' => 'rented']);

        $this->actingAs($landlord);
        Filament::setCurrentPanel('dashboard');

        Livewire::test(ViewRoom::class, ['record' => $room->id])
            ->callAction('linkTenant', data: ['tenant_id' => $newTenant->id]);

        Mail::assertQueued(
            ReviewInvitationMail::class,
            fn (ReviewInvitationMail $mail) => $mail->hasTo('vorige@example.test')
        );
        Mail::assertQueued(ReviewInvitationMail::class, 1);
    }

    public function test_no_mail_when_the_tenant_already_reviewed_the_room(): void
    {
        Mail::fake();
        $room = Room::factory()->create();
        $tenant = User::factory()->create();
        RoomReview::factory()->forRoom($room)->create(['tenant_id' => $tenant->id]);

        ReviewInvitation::issueFor($room, $tenant->id);

        Mail::assertNotQueued(ReviewInvitationMail::class);
    }

    public function test_the_mail_shows_each_criterion_with_clickable_one_to_five_deep_links(): void
    {
        $room = Room::factory()->create(['title' => 'Zonnig kot aan het park']);
        $invitation = ReviewInvitation::factory()->forRoom($room)->create();

        $mail = new ReviewInvitationMail($invitation);

        $mail->assertSeeInHtml('Hoe was je kot?');
        $mail->assertSeeInHtml('Zonnig kot aan het park');

        // All four criteria are shown.
        foreach (['Hygiëne', 'Grootte', 'Prijs-kwaliteit', 'Communicatie verhuurder'] as $label) {
            $mail->assertSeeInHtml($label);
        }

        // Each criterion deep-links 1..5 into the survey with that answer + token.
        $mail->assertSeeInHtml($invitation->token);
        foreach (['score_hygiene', 'score_size', 'score_value', 'score_communication'] as $field) {
            $mail->assertSeeInHtml($field.'=1');
            $mail->assertSeeInHtml($field.'=5');
        }

        // Score-only: no free-text field.
        $mail->assertDontSeeInHtml('<textarea');
    }

    public function test_survey_preselects_the_radio_from_a_query_param(): void
    {
        $invitation = ReviewInvitation::factory()->create();

        $html = $this->get(route('reviews.create', [$invitation, 'score_hygiene' => 4]))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/name="score_hygiene"\s+value="4"[^>]*\bchecked\b/',
            $html,
            'The hygiene=4 radio should be pre-selected from the query param.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/name="score_hygiene"\s+value="3"[^>]*\bchecked\b/',
            $html,
            'Only the value from the query param should be pre-selected.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/name="score_size"\s+value="\d"[^>]*\bchecked\b/',
            $html,
            'Criteria without a query param must stay unselected.'
        );
    }
}
