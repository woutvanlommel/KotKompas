@php
/*
|--------------------------------------------------------------------------
| KotKompas — Base Auth Email Layout
|--------------------------------------------------------------------------
|
| This file is the MASTER LAYOUT for all KotKompas authentication emails:
| email verification, password reset, and any future auth-related mail.
|
| It works in two ways:
|
|   1. STANDALONE — pass PHP variables from your Mailable class:
|
|       return new Mailable()
|           ->view('mailing.auth')
|           ->with([
|               'heading'    => 'Bevestig je e-mailadres',
|               'lines'      => ['Klik op de knop hieronder om je e-mailadres te bevestigen.'],
|               'actionText' => 'E-mailadres bevestigen',
|               'actionUrl'  => $verificationUrl,
|               'expiresIn'  => '60 minuten',
|           ]);
|
|   2. EXTENDED — create a child template that extends this layout:
|
|       // resources/views/mailing/verify-email.blade.php
|       @extends('mailing.auth')
|
|       @section('heading')
|           <h1 style="margin:0 0 20px;font-size:22px;font-weight:bold;color:#0f1720;letter-spacing:-0.02em;line-height:1.2;">
|               Bevestig je e-mailadres
|           </h1>
|       @endsection
|
|       @section('body')
|           <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#586573;">
|               Klik op de knop hieronder om je account te activeren.
|           </p>
|       @endsection
|
|       @section('action')
|           <a href="{{ $verificationUrl }}"
|              style="display:inline-block;background:#ff6700;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 24px;border-radius:4px;letter-spacing:0.02em;">
|               E-mailadres bevestigen
|           </a>
|       @endsection
|
|--------------------------------------------------------------------------
| Available @yield sections (all optional):
|
|   heading   — The main heading. Falls back to $heading.
|   body      — Body paragraphs. Falls back to $lines[].
|   action    — CTA button area. Falls back to $actionText + $actionUrl button.
|   extra     — Additional content after the expiry line. Empty by default.
|
|--------------------------------------------------------------------------
| Available PHP variables (all optional):
|
|   $heading      string        'Actie vereist'   Heading fallback.
|   $lines        string[]      []                Body paragraph fallback.
|   $actionText   string|null   null              CTA button label.
|   $actionUrl    string|null   null              CTA button URL.
|                                                 Also used for the plain-text fallback.
|   $expiresIn    string|null   null              Expiry duration shown below the button,
|                                                 e.g. '60 minuten'. Hidden when null.
|   $disclaimer   string|null   null              Override the default security disclaimer.
|
|--------------------------------------------------------------------------
| Structural elements that are ALWAYS rendered (cannot be suppressed):
|
|   - Plain-text URL fallback  (when $actionUrl is set)
|   - Security disclaimer      (above the footer)
|
| These are security requirements. Auth emails must always provide a
| plain-text link and inform the recipient they can ignore the email
| if they did not request the action.
|
|--------------------------------------------------------------------------
| Design tokens (inline — email clients strip <style> blocks):
|
|   Background:        #ebebeb   (base-een / Platinum)
|   Card background:   #ffffff
|   Header + stripe:   #004e98   (primary / Steel Azure — fixed, no $type)
|   CTA button:        #ff6700   (accent / Pumpkin Spice)
|   Primary text:      #0f1720   (ink)
|   Muted text:        #586573   (ink-soft)
|   Footer background: #f7f7f7
|   Divider:           #e2e2e2
|   Font:              Arial, Helvetica, sans-serif
|
*/
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Disable auto-linking of phone numbers / addresses in Apple Mail --}}
    <meta name="format-detection" content="telephone=no, address=no, email=no, date=no">
    <title>{{ config('app.name', 'KotKompas') }}</title>
</head>
<body bgcolor="#ebebeb" style="margin:0;padding:24px 0;background:#ebebeb;font-family:Arial,Helvetica,sans-serif;color:#0f1720;">

    {{-- ── Outer wrapper: centres the card in all email clients ── --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:24px 16px;">

                {{-- ── Card: max 560px, white, rounded (where supported) ── --}}
                {{-- MSO conditional enforces 560px in Outlook (ignores CSS max-width) --}}
                <!--[if mso]><table role="presentation" width="560" align="center" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,32,0.08);">

                    {{-- ══ HEADER ══════════════════════════════════════════════ --}}
                    {{-- Navy bar with the KotKompas wordmark.                  --}}
                    <tr>
                        <td style="background:#004e98;padding:20px 28px;">
                            <span style="color:#ffffff;font-size:18px;font-weight:bold;letter-spacing:-0.02em;">
                                KotKompas
                            </span>
                        </td>
                    </tr>

                    {{-- ══ ACCENT STRIPE ═══════════════════════════════════════ --}}
                    {{-- Always navy — auth emails carry no severity type.       --}}
                    <tr>
                        <td style="height:4px;background:#004e98;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- ══ CONTENT ══════════════════════════════════════════════ --}}
                    <tr>
                        <td style="padding:32px 28px;">

                            {{-- ── HEADING ──────────────────────────────────────── --}}
                            {{-- Override with @section('heading', 'Your title')    --}}
                            {{-- or @section('heading') ... @endsection             --}}
                            @hasSection('heading')
                                @yield('heading')
                            @else
                                <h1 style="margin:0 0 20px;font-size:22px;font-weight:bold;color:#0f1720;letter-spacing:-0.02em;line-height:1.2;">
                                    {{ $heading ?? 'Actie vereist' }}
                                </h1>
                            @endif

                            {{-- ── BODY ────────────────────────────────────────── --}}
                            {{-- Override with @section('body') ... @endsection     --}}
                            {{-- Fallback renders each string in $lines[] as a <p>  --}}
                            @hasSection('body')
                                @yield('body')
                            @else
                                @foreach($lines ?? [] as $line)
                                    <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#586573;">
                                        {{ $line }}
                                    </p>
                                @endforeach
                            @endif

                            {{-- ── ACTION (CTA BUTTON) ─────────────────────────── --}}
                            {{-- Override with @section('action') ... @endsection   --}}
                            {{-- Fallback renders an orange button when $actionUrl  --}}
                            {{-- and $actionText are both provided.                 --}}
                            @hasSection('action')
                                <div style="margin:24px 0 0;">
                                    @yield('action')
                                </div>
                            @elseif(!empty($actionUrl) && !empty($actionText))
                                <p style="margin:24px 0 0;">
                                    <a href="{{ $actionUrl }}"
                                       style="display:inline-block;background:#ff6700;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 24px;border-radius:4px;letter-spacing:0.02em;">
                                        {{ $actionText }}
                                    </a>
                                </p>
                            @endif

                            {{-- ── PLAIN-TEXT URL FALLBACK ─────────────────────── --}}
                            {{-- Always rendered when $actionUrl is set.            --}}
                            {{-- Security requirement: the recipient must always    --}}
                            {{-- be able to copy the link even if the button fails. --}}
                            @if(!empty($actionUrl))
                                <p style="margin:20px 0 0;font-size:13px;color:#586573;line-height:1.5;">
                                    Werkt de knop niet? Kopieer de link hieronder:<br>
                                    <a href="{{ $actionUrl }}"
                                       style="color:#004e98;word-break:break-all;font-size:12px;">
                                        {{ $actionUrl }}
                                    </a>
                                </p>
                            @endif

                            {{-- ── EXPIRY LINE ──────────────────────────────────── --}}
                            {{-- Rendered when $expiresIn is set, e.g. '60 minuten' --}}
                            @if(!empty($expiresIn))
                                <p style="margin:16px 0 0;font-size:13px;color:#586573;">
                                    Deze link vervalt over <strong style="color:#0f1720;">{{ $expiresIn }}</strong>.
                                </p>
                            @endif

                            {{-- ── EXTRA ───────────────────────────────────────── --}}
                            {{-- Optional section for any content after the expiry. --}}
                            {{-- Example: a secondary note, a list of next steps.  --}}
                            @yield('extra')

                            {{-- ── SECURITY DISCLAIMER ─────────────────────────── --}}
                            {{-- Always rendered — cannot be suppressed by child    --}}
                            {{-- templates. Informs the recipient they can safely   --}}
                            {{-- ignore this email if they did not request it.      --}}
                            <p style="margin:28px 0 0;padding-top:20px;border-top:1px solid #e2e2e2;font-size:12px;color:#8d8d8d;line-height:1.6;">
                                {{ $disclaimer ?? 'Heb je dit niet aangevraagd? Dan kun je deze e-mail veilig negeren. Je account blijft ongewijzigd.' }}
                            </p>

                        </td>
                    </tr>

                    {{-- ══ FOOTER ══════════════════════════════════════════════ --}}
                    {{-- Muted bar with app attribution.                         --}}
                    {{-- No unsubscribe link — security emails cannot be opted   --}}
                    {{-- out of by the recipient.                                --}}
                    <tr>
                        <td style="padding:16px 28px;background:#f7f7f7;border-top:1px solid #e2e2e2;font-size:12px;color:#8d8d8d;line-height:1.5;">
                            Verstuurd via het {{ config('app.name', 'KotKompas') }}-platform.
                        </td>
                    </tr>

                </table>
                <!--[if mso]></td></tr></table><![endif]-->
                {{-- /Card --}}

            </td>
        </tr>
    </table>
    {{-- /Outer wrapper --}}

</body>
</html>
