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
        // Verlopen of al ingevuld: de create-pagina toont de juiste status.
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
                // Atomaire claim vóór de insert: bij twee gelijktijdige
                // submits wint er precies één — de unique index op
                // room_reviews beschermt niet wanneer tenant_id null is.
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
            // Oude tweede uitnodiging: er bestaat al een beoordeling van deze
            // huurder voor dit kot — de uitnodiging is dan alsnog voldaan.
            $invitation->forceFill(['completed_at' => now()])->save();
        }

        return redirect()->route('reviews.create', $invitation);
    }
}
