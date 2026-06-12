<?php

// Observer on RoomReview: any change to a review immediately updates the
// cached scores of the room, building and landlord (KotScoreService).
// The daily app:recompute-kotscores cron catches recency drift.
//
// ShouldHandleEventsAfterCommit: the survey submit creates the review inside
// a transaction; the recompute should happen outside it, otherwise it holds
// locks on rooms/buildings/users while computing.

namespace App\Observers;

use App\Models\RoomReview;
use App\Services\KotScoreService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RoomReviewObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private KotScoreService $kotScoreService) {}

    public function created(RoomReview $review): void
    {
        $this->kotScoreService->recomputeForReview($review);
    }

    public function updated(RoomReview $review): void
    {
        $this->kotScoreService->recomputeForReview($review);

        // Moved review (room or landlord corrected): the review's previous
        // owner must also have their cache invalidated.
        if ($review->wasChanged('room_id') || $review->wasChanged('landlord_id')) {
            $this->kotScoreService->recomputeFor(
                (int) $review->getOriginal('room_id'),
                (int) $review->getOriginal('landlord_id'),
            );
        }
    }

    public function deleted(RoomReview $review): void
    {
        $this->kotScoreService->recomputeForReview($review);
    }
}
