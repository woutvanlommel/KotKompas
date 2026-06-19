<?php

namespace App\Mail;

use App\Models\ReviewInvitation;
use App\Models\RoomReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the ex-tenant when a rental ends, so they can score their kot.
 * The mail itself shows the 1–5 selectors per criterion: each number is a
 * deep-link into the survey with that answer pre-filled.
 */
class ReviewInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ReviewInvitation $invitation)
    {
        // Hold the queued job until the surrounding DB transaction commits,
        // so a rolled-back unlink never sends.
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Hoe was je kot? Geef je beoordeling');
    }

    public function content(): Content
    {
        return new Content(view: 'mailing.review-invitation', with: [
            'invitation' => $this->invitation,
            'criteria' => RoomReview::CRITERIA,
        ]);
    }
}
