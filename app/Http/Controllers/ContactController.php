<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Handle a contact form submission.
     *
     * BACKEND DEV — this is the wiring target. The page already POSTs here
     * with: name, email, subject, message, consent, website (honeypot), _token.
     *
     * TODO:
     *   - Send the mail (Mailable + Mail::to(...)) and/or persist the message.
     *   - Configure MAIL_* env + a recipient address.
     *   - Optionally throttle this route (->middleware('throttle:5,1') in web.php).
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

        // TODO(backend): deliver $validated (mail and/or DB). Set session('error') on failure.

        return back()->with('success', 'Bedankt! Je bericht is verstuurd — we antwoorden binnen 24 uur.');
    }
}
