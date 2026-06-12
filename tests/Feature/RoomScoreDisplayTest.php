<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomReview;
use App\Services\KotScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomScoreDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_page_shows_kotscore_badge_and_breakdown(): void
    {
        $room = Room::factory()->create();
        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 4,
            'score_size' => 4,
            'score_value' => 4,
            'score_communication' => 2,
        ]);

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Wat zeggen ex-huurders?')
            ->assertSee('4,0')        // kotscore (badge + section)
            ->assertSee('1 beoordeling')
            ->assertSee('Hygiëne')
            // Communication is asked in the survey and counts toward the
            // landlord score, but is never displayed on room pages.
            ->assertDontSee('Communicatie verhuurder');
    }

    public function test_detail_page_hides_kotscore_without_reviews(): void
    {
        $room = Room::factory()->create();

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertDontSee('Wat zeggen ex-huurders?')
            ->assertDontSee('Kotscore')
            ->assertDontSee('beoordelingen');
    }

    public function test_criteria_breakdown_uses_the_same_recency_weights_as_the_kotscore(): void
    {
        $room = Room::factory()->create();

        $this->travelTo(now()->subYears(3));
        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 1, 'score_size' => 1, 'score_value' => 1, 'score_communication' => 1,
        ]);
        $this->travelBack();

        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4, 'score_communication' => 4,
        ]);

        // (1 × 1 + 2 × 4) / 3 — the old review counts at half weight.
        $breakdown = app(KotScoreService::class)->criteriaBreakdown($room);

        $this->assertSame([
            'hygiene' => 3.0,
            'size' => 3.0,
            'value' => 3.0,
        ], $breakdown);

        // And the total on the page does not contradict the breakdown.
        $this->assertSame(3.0, $room->refresh()->score);
    }
}
