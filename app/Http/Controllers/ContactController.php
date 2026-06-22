<?php

namespace App\Http\Controllers;

use App\Mail\SupportContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show(): View
    {
        return view('contact');
    }

    /**
     * Handle a contact form submission. Delivers the message to the support
     * inbox via SupportContactMail (reply-to the sender), mirroring the
     * dashboard support-contact flow. Sent synchronously — not queued — so a
     * delivery failure surfaces immediately to the visitor.
     */
    public function store(Request $request): RedirectResponse
    {
        // Honeypot: silently drop bot submissions (field must stay empty).
        if ($request->filled('website')) {
            return back()->with('success', 'Bedankt voor je bericht!');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
        ]);

        try {
            Mail::to(config('mail.support_address'))->send(new SupportContactMail(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                subjectLine: $validated['subject'],
                body: $validated['message'],
                channel: 'website',
            ));
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Er ging iets mis bij het versturen. Probeer het later opnieuw.');
        }

        return back()->with('success', 'Bedankt! Je bericht is verstuurd — we antwoorden binnen 24 uur.');
    }
}
