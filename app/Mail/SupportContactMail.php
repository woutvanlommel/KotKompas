<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportContactMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $channel  Where the message originates: 'dashboard' (a
     *                           logged-in landlord via the dashboard) or
     *                           'website' (the public /contact form). Drives
     *                           the subject and body wording.
     */
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subjectLine,
        public string $body,
        public string $channel = 'dashboard',
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->channel === 'website' ? 'Contact via website' : 'Contact verhuurder';

        return new Envelope(
            subject: $prefix.': '.$this->subjectLine,
            // Support can reply straight to the sender.
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-contact',
        );
    }
}
