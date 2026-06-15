{{--
|--------------------------------------------------------------------------
| KotKompas — Base Mail Notification Layout
|--------------------------------------------------------------------------
|
| This file is the MASTER LAYOUT for all KotKompas notification emails.
| It works in two ways:
|
|   1. STANDALONE — pass PHP variables from your Mailable class:
|
|       return new Mailable()
|           ->view('mailing.notification')
|           ->with([
|               'greeting'       => 'Hallo Jan!',
|               'lines'          => ['Je aanvraag is goedgekeurd.'],
|               'actionText'     => 'Bekijk je dashboard',
|               'actionUrl'      => url('/dashboard'),
|               'type'           => 'success',   // info|success|warning|error
|               'unsubscribeUrl' => unsubscribeUrl($user),
|           ]);
|
|   2. EXTENDED — create a new mail template that extends this layout:
|
|       {{-- resources/views/mailing/contract-ready.blade.php --}}
|       @extends('mailing.notification')
|
|       @section('heading', 'Je contract is klaar')
|
|       @section('body')
|           <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#586573;">
|               Beste {{ $tenantName }}, je contract staat klaar om te ondertekenen.
|           </p>
|       @endsection
|
|       @section('action')
|           <a href="{{ $contractUrl }}" style="...">Onderteken contract</a>
|       @endsection
|
|       @section('extra')
|           {{-- Any content rendered after the action button --}}
|       @endsection
|
|--------------------------------------------------------------------------
| Available @yield sections (all optional):
|
|   @section('heading')   — The main heading. Falls back to $greeting.
|   @section('body')      — Body paragraphs. Falls back to $lines[].
|   @section('action')    — CTA button area. Falls back to $actionText/$actionUrl.
|   @section('extra')     — Additional content after the action. Empty by default.
|
|--------------------------------------------------------------------------
| Available PHP variables (all optional):
|
|   $greeting        string        'Hallo!'        Heading fallback.
|   $lines           string[]      []              Body paragraph fallback.
|   $actionText      string|null   null            CTA button label.
|   $actionUrl       string|null   null            CTA button URL.
|   $type            string        'info'          Accent stripe colour:
|                                                  info|success|warning|error
|   $unsubscribeUrl  string|null   null            Footer unsubscribe link.
|
|--------------------------------------------------------------------------
| Design tokens (inline — email clients strip <style> blocks):
|
|   Background:        #ebebeb   (base-een / Platinum)
|   Card background:   #ffffff
|   Header:            #004e98   (primary / Steel Azure)
|   CTA button:        #ff6700   (accent / Pumpkin Spice)
|   Primary text:      #0f1720   (ink)
|   Muted text:        #586573   (ink-soft)
|   Footer background: #f7f7f7
|   Divider:           #e2e2e2
|   Font:              Arial, Helvetica, sans-serif (email-safe fallback for area-normal)
|
--}}

@php
$stripeColor = match($type ?? 'info') {
    'success' => '#16a34a',
    'warning' => '#ff6700',
    'error'   => '#dc2626',
    default   => '#004e98',   // 'info' and any unknown value → Steel Azure
};
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
<body style="margin:0;padding:24px 0;background:#ebebeb;font-family:Arial,Helvetica,sans-serif;color:#0f1720;">

    {{-- ── Outer wrapper: centres the card in all email clients ── --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:24px 16px;">

                {{-- ── Card: max 560px, white, rounded (where supported) ── --}}
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
                    {{-- 4px colour bar driven by $type (info/success/warning/error). --}}
                    <tr>
                        <td style="height:4px;background:{{ $stripeColor }};font-size:0;line-height:0;">&nbsp;</td>
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
                                    {{ $greeting ?? 'Hallo!' }}
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

                            {{-- ── EXTRA ───────────────────────────────────────── --}}
                            {{-- Optional section for any content after the button. --}}
                            {{-- Example: a secondary link, a disclaimer, a list.  --}}
                            @yield('extra')

                        </td>
                    </tr>

                    {{-- ══ FOOTER ══════════════════════════════════════════════ --}}
                    {{-- Muted bar with app attribution and optional unsubscribe. --}}
                    <tr>
                        <td style="padding:16px 28px;background:#f7f7f7;border-top:1px solid #e2e2e2;font-size:12px;color:#8d8d8d;line-height:1.5;">
                            Verstuurd via het {{ config('app.name', 'KotKompas') }}-platform.
                            @if(!empty($unsubscribeUrl))
                                &nbsp;·&nbsp;
                                <a href="{{ $unsubscribeUrl }}"
                                   style="color:#8d8d8d;text-decoration:underline;">
                                    Uitschrijven
                                </a>
                            @endif
                        </td>
                    </tr>

                </table>
                {{-- /Card --}}

            </td>
        </tr>
    </table>
    {{-- /Outer wrapper --}}

</body>
</html>
