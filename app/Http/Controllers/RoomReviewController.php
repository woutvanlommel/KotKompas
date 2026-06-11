<?php

namespace App\Http\Controllers;

use App\Models\ReviewInvitation;
use App\Models\RoomReview;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The room score survey behind the token link. Anonymous to the outside world:
 * tenant_id is only stored internally (dedup and fraud control).
 */
class RoomReviewController extends Controller
{
    public function create(ReviewInvitation $invitation): View
    {
        $invitation->load('room.building');

        return view('reviews.create', ['invitation' => $invitation]);
    }

    public function store(Request $request, ReviewInvitation $invitation): RedirectResponse
    {
        // Expired or already completed: the create page shows the right status.
        if (! $invitation->isOpen()) {
            return redirect()->route('reviews.create', $invitation);
        }

        // Honeypot: silently drop bot submissions (field must stay empty).
        if ($request->filled('website')) {
            return redirect()->route('reviews.create', $invitation);
        }

        $validated = $request->validate([
            'score_hygiene' => ['required', 'integer', 'between:1,5'],
            'score_size' => ['required', 'integer', 'between:1,5'],
            'score_value' => ['required', 'integer', 'between:1,5'],
            'score_communication' => ['required', 'integer', 'between:1,5'],
        ]);

        try {
            DB::transaction(function () use ($invitation, $validated) {
                // Atomic claim before the insert: with two concurrent
                // submits exactly one wins — the unique index on
                // room_reviews does not protect when tenant_id is null.
                $claimed = ReviewInvitation::query()
                    ->whereKey($invitation->id)
                    ->whereNull('completed_at')
                    ->update(['completed_at' => now()]);

                if ($claimed === 0) {
                    return;
                }

                RoomReview::create([
                    ...$validated,
                    'room_id' => $invitation->room_id,
                    'landlord_id' => $invitation->landlord_id,
                    'tenant_id' => $invitation->tenant_id,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // Old second invitation: a review from this tenant for this
            // room already exists — the invitation is still marked fulfilled.
            $invitation->forceFill(['completed_at' => now()])->save();
        }

        return redirect()->route('reviews.create', $invitation);
    }
}
