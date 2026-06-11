<?php

// Observer op RoomReview: elke wijziging aan een beoordeling werkt meteen de
// cached scores van het kot, het gebouw en de verhuurder bij (KotScoreService).
// De dagelijkse app:recompute-kotscores vangt de recency-drift op.

namespace App\Observers;

use App\Models\RoomReview;
use App\Services\KotScoreService;

class RoomReviewObserver
{
    public function __construct(private KotScoreService $kotScoreService) {}

    public function created(RoomReview $review): void
    {
        $this->kotScoreService->recomputeForReview($review);
    }

    public function updated(RoomReview $review): void
    {
        $this->kotScoreService->recomputeForReview($review);

        // Verplaatste beoordeling (kot of verhuurder gecorrigeerd): ook de
        // oude eigenaar van de review moet zijn cache kwijtraken.
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
