<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomReview;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomReviewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_room_and_landlord(): void
    {
        $room = Room::factory()->create();
        $review = RoomReview::factory()->forRoom($room)->create();

        $this->assertTrue($review->room->is($room));
        $this->assertSame($room->building->landlord_id, $review->landlord->id);
        $this->assertTrue($room->reviews->first()->is($review));
        $this->assertTrue($review->landlord->landlordReviews->first()->is($review));
    }

    public function test_room_score_averages_kot_criteria_without_communication(): void
    {
        $review = RoomReview::factory()->create([
            'score_hygiene' => 4,
            'score_size' => 3,
            'score_value' => 5,
            'score_communication' => 1, // telt niet mee in de kotscore
        ]);

        $this->assertSame(4.0, $review->room_score);
    }

    public function test_tenant_can_only_review_a_room_once(): void
    {
        $room = Room::factory()->create();
        $tenant = User::factory()->create();

        RoomReview::factory()->forRoom($room)->create(['tenant_id' => $tenant->id]);

        $this->expectException(QueryException::class);
        RoomReview::factory()->forRoom($room)->create(['tenant_id' => $tenant->id]);
    }

    public function test_deleting_a_room_deletes_its_reviews(): void
    {
        $room = Room::factory()->create();
        RoomReview::factory()->forRoom($room)->create();

        $room->delete();

        $this->assertDatabaseCount('room_reviews', 0);
    }
}
