<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KotScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_review_updates_cached_scores_via_observer(): void
    {
        $room = Room::factory()->create();

        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 4,
            'score_size' => 4,
            'score_value' => 4,
            'score_communication' => 2,
        ]);

        $room->refresh();
        $this->assertSame(4.0, $room->score);
        $this->assertSame(1, $room->reviews_count);
        $this->assertSame(4.0, $room->building->refresh()->score);
        $this->assertSame(3.0, $room->building->landlord->refresh()->landlord_score); // (4 + 2) / 2
    }

    public function test_kotscore_weighs_recent_reviews_double(): void
    {
        $room = Room::factory()->create();

        $this->travelTo(now()->subYears(3));
        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 2, 'score_size' => 2, 'score_value' => 2,
        ]);
        $this->travelBack();

        RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5,
        ]);

        // (1 × 2 + 2 × 5) / 3 — the old review counts at half weight.
        $this->assertSame(4.0, $room->refresh()->score);
    }

    public function test_bayesian_score_pulls_towards_platform_mean(): void
    {
        $top = Room::factory()->create();
        $flop = Room::factory()->create();
        RoomReview::factory()->forRoom($top)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5,
        ]);
        RoomReview::factory()->forRoom($flop)->create([
            'score_hygiene' => 1, 'score_size' => 1, 'score_value' => 1,
        ]);

        $this->artisan('app:recompute-kotscores')->assertSuccessful();

        // Platformgemiddelde = 3.0; één review (gewicht 2) tegen confidence 5.
        $this->assertSame(5.0, $top->refresh()->score);
        $this->assertSame(3.57, $top->score_bayesian); // (5×3 + 2×5) / 7
        $this->assertSame(1.0, $flop->refresh()->score);
        $this->assertSame(2.43, $flop->score_bayesian); // (5×3 + 2×1) / 7
    }

    public function test_building_score_aggregates_all_its_rooms(): void
    {
        $building = Building::factory()->create();
        $roomA = Room::factory()->for($building)->create();
        $roomB = Room::factory()->for($building)->create();

        RoomReview::factory()->forRoom($roomA)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5,
        ]);
        RoomReview::factory()->forRoom($roomB)->create([
            'score_hygiene' => 3, 'score_size' => 3, 'score_value' => 3,
        ]);

        $building->refresh();
        $this->assertSame(4.0, $building->score);
        $this->assertSame(2, $building->reviews_count);
    }

    public function test_deleting_a_review_recomputes_caches(): void
    {
        $room = Room::factory()->create();
        $keep = RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 5, 'score_size' => 5, 'score_value' => 5,
        ]);
        $drop = RoomReview::factory()->forRoom($room)->create([
            'score_hygiene' => 1, 'score_size' => 1, 'score_value' => 1,
        ]);

        $drop->delete();

        $room->refresh();
        $this->assertSame(5.0, $room->score);
        $this->assertSame(1, $room->reviews_count);

        $keep->delete();

        $room->refresh();
        $this->assertNull($room->score);
        $this->assertNull($room->score_bayesian);
        $this->assertSame(0, $room->reviews_count);
    }

    public function test_moving_a_review_also_recomputes_the_old_room(): void
    {
        $oldRoom = Room::factory()->create();
        $newRoom = Room::factory()->create();
        $review = RoomReview::factory()->forRoom($oldRoom)->create([
            'score_hygiene' => 4, 'score_size' => 4, 'score_value' => 4,
        ]);

        $review->update([
            'room_id' => $newRoom->id,
            'landlord_id' => $newRoom->building->landlord_id,
        ]);

        $this->assertNull($oldRoom->refresh()->score);
        $this->assertSame(0, $oldRoom->reviews_count);
        $this->assertNull($oldRoom->building->refresh()->score);
        $this->assertSame(4.0, $newRoom->refresh()->score);
        $this->assertSame(1, $newRoom->reviews_count);
    }

    public function test_recompute_all_heals_building_after_room_cascade_delete(): void
    {
        $building = Building::factory()->create();
        $room = Room::factory()->for($building)->create();
        RoomReview::factory()->forRoom($room)->create();

        $this->assertSame(1, $building->refresh()->reviews_count);

        // DB-cascade verwijdert de review zonder observer — de cache blijft
        // stale tot de dagelijkse recompute het gebouw zelf langsloopt.
        $room->delete();
        $this->assertSame(1, $building->refresh()->reviews_count);

        $this->artisan('app:recompute-kotscores')->assertSuccessful();

        $building->refresh();
        $this->assertNull($building->score);
        $this->assertSame(0, $building->reviews_count);
    }
}
