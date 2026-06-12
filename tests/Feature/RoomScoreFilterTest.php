<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomScoreFilterTest extends TestCase
{
    use RefreshDatabase;

    private function reviewTimes(Room $room, int $times, int $kot, int $communication = 3): void
    {
        for ($i = 0; $i < $times; $i++) {
            RoomReview::factory()->forRoom($room)->create([
                'score_hygiene' => $kot,
                'score_size' => $kot,
                'score_value' => $kot,
                'score_communication' => $communication,
            ]);
        }
    }

    public function test_sorting_by_score_ranks_on_bayesian_not_on_display_score(): void
    {
        // consistent: 6 reviews, gemiddeld 4,83 — single: één perfecte review.
        // Op weergavescore wint single (5,0 > 4,83); op score_bayesian wint
        // consistent — precies waarvoor die kolom bestaat.
        $consistent = Room::factory()->create();
        $this->reviewTimes($consistent, 5, kot: 5);
        $this->reviewTimes($consistent, 1, kot: 4);

        $single = Room::factory()->create();
        $this->reviewTimes($single, 1, kot: 5);

        $weak = Room::factory()->create();
        $this->reviewTimes($weak, 4, kot: 1);

        $unreviewed = Room::factory()->create();

        // Bayesians settelen op het uiteindelijke platformgemiddelde —
        // in productie doet de nachtelijke recompute dit.
        $this->artisan('app:recompute-kotscores')->assertSuccessful();

        $this->assertGreaterThan($consistent->refresh()->score, $single->refresh()->score);
        $this->assertGreaterThan($single->score_bayesian, $consistent->score_bayesian);

        $ids = $this->get('/koten?sort=score')
            ->assertOk()
            ->viewData('rooms')
            ->pluck('id')
            ->all();

        $this->assertSame([$consistent->id, $single->id, $weak->id, $unreviewed->id], $ids);
    }

    public function test_minimum_score_filter_excludes_lower_scored_and_unreviewed_rooms(): void
    {
        $top = Room::factory()->create();
        $this->reviewTimes($top, 1, kot: 5);          // score 5,0
        $decent = Room::factory()->create();
        $this->reviewTimes($decent, 1, kot: 4);       // score 4,0
        $unreviewed = Room::factory()->create();

        $ids = $this->get('/koten?score_min=4.5')->viewData('rooms')->pluck('id')->all();
        $this->assertSame([$top->id], $ids);

        $ids = $this->get('/koten?score_min=4')->viewData('rooms')->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$top->id, $decent->id], $ids);
        $this->assertNotContains($unreviewed->id, $ids);
    }

    public function test_invalid_score_min_is_ignored(): void
    {
        Room::factory()->count(2)->create();

        $rooms = $this->get('/koten?score_min=onzin')->assertOk()->viewData('rooms');

        $this->assertCount(2, $rooms);
    }

    public function test_featured_rooms_on_home_rank_best_reviewed_first(): void
    {
        $good = Room::factory()->create();
        $this->reviewTimes($good, 1, kot: 5);
        $mediocre = Room::factory()->create();
        $this->reviewTimes($mediocre, 1, kot: 2);
        $unreviewed = Room::factory()->create();

        $this->artisan('app:recompute-kotscores')->assertSuccessful();

        $ids = $this->get('/')
            ->assertOk()
            ->viewData('featuredRooms')
            ->pluck('id')
            ->all();

        $this->assertSame([$good->id, $mediocre->id, $unreviewed->id], $ids);
    }

    public function test_score_sort_carries_through_pagination(): void
    {
        Room::factory()->count(13)->create();
        $reviewed = Room::factory()->create();
        $this->reviewTimes($reviewed, 1, kot: 5);

        $pageOne = $this->get('/koten?sort=score')->assertOk()->viewData('rooms');
        $this->assertSame($reviewed->id, $pageOne->first()->id);
        $this->assertStringContainsString('sort=score', $pageOne->url(2));

        $pageTwo = $this->get('/koten?sort=score&page=2')->assertOk()->viewData('rooms');
        $this->assertCount(2, $pageTwo); // 14 koten, 12 per pagina
        $this->assertNotContains($reviewed->id, $pageTwo->pluck('id'));
    }

    public function test_listing_cards_show_the_score_badge(): void
    {
        $room = Room::factory()->create();
        $this->reviewTimes($room, 1, kot: 4);

        $this->get('/koten')
            ->assertOk()
            ->assertSee('4,0')
            ->assertSee('(1)');
    }
}
