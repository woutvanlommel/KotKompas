@props(['overHero' => false])

@php
    $links = [
        ['label' => 'Koten',   'href' => '#koten',           'active' => false],
        ['label' => 'FAQ',     'href' => route('faq'),       'active' => request()->routeIs('faq')],
        ['label' => 'Contact', 'href' => route('contact'),   'active' => request()->routeIs('contact')],
    ];
@endphp

{{-- ════════════════════════════════════════════════════════════════════════
     PUBLIC NAV — editorial top-bar, NOT a dark pill.
     · Two-tone wordmark (ink + accent) = the signature mark.
     · Mono-numeral links with an active baseline-wipe cue.
     · Two-CTA hierarchy: Inloggen (ghost) · "Verhuur je kot" (.kk-cta primary).
     · Hide-on-scroll-down / show-on-scroll-up (data-autohide → JS in app.ts).
     · Fullscreen NAVY overlay menu on mobile (scroll-lock + focus + ESC).
     ════════════════════════════════════════════════════════════════════════ --}}
<header
    class="kk-topnav pointer-events-none fixed inset-x-0 top-0 z-50"
    data-autohide
    @if ($overHero) data-over-hero @endif
>
    <div class="pointer-events-auto mx-auto flex max-w-[88rem] items-center justify-between gap-4 px-5 py-4 sm:px-8 sm:py-5">

        {{-- ── Wordmark (two-tone) ─────────────────────────────────────────── --}}
        <a href="{{ url('/') }}"
           class="kk-topnav-mark group inline-flex shrink-0 items-baseline gap-px text-[1.35rem] font-medium leading-none tracking-[-0.04em] sm:text-2xl"
           aria-label="KotKompas — naar de homepagina">
            <span class="text-ink transition-colors">Kot</span><span class="text-accent-500 transition-colors group-hover:text-accent-600">Kompas</span><span class="ml-0.5 h-1.5 w-1.5 translate-y-[-0.55em] rounded-full bg-accent-500 transition-transform duration-300 group-hover:scale-150"></span>
        </a>

        {{-- ── Desktop link rail (mono index + label, active baseline-wipe) ── --}}
        <nav class="hidden items-center gap-1 md:flex" aria-label="Hoofdnavigatie">
            @foreach ($links as $i => $link)
                <a href="{{ $link['href'] }}"
                   @if ($link['active']) aria-current="page" @endif
                   class="kk-navlink group relative inline-flex items-baseline gap-1.5 rounded-full px-3.5 py-2 text-[0.9rem] font-medium text-ink-soft transition-colors hover:text-ink @if ($link['active']) is-active text-ink @endif">
                    <span class="font-mono text-[0.62rem] text-ink-soft/60 group-hover:text-accent-500">0{{ $i + 1 }}</span>
                    <span>{{ $link['label'] }}</span>
                    <span class="kk-navlink-wipe pointer-events-none absolute inset-x-3.5 bottom-1 h-px origin-left bg-secondary-400"></span>
                </a>
            @endforeach
        </nav>

        {{-- ── CTA cluster: ghost Inloggen · primary Verhuur je kot ────────── --}}
        <div class="flex items-center gap-2.5">
            <a href="{{ url('/dashboard/login') }}"
               class="hidden h-11 items-center rounded-full border border-hairline px-5 text-[0.85rem] font-medium text-ink transition-colors hover:border-ink/30 hover:bg-ink/[0.04] sm:inline-flex">
                Inloggen
            </a>

            {{-- Primary: reuse the auth-signature pill + sliding arrow chip --}}
            <a href="{{ url('/dashboard/register') }}"
               class="kk-cta kk-cta--ink hidden sm:inline-flex"
               data-magnetic="0.18">
                Verhuur je kot
                <span class="kk-cta-chip">
                    <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </a>

            {{-- Mobile menu toggle (opens fullscreen overlay) --}}
            <button type="button"
                    class="kk-menu-toggle inline-flex h-11 w-11 items-center justify-center rounded-full border border-hairline bg-canvas/70 text-ink backdrop-blur-md transition-colors hover:bg-canvas md:hidden"
                    aria-controls="kk-menu-overlay" aria-expanded="false">
                <span class="sr-only">Menu openen</span>
                <span class="kk-burger" aria-hidden="true">
                    <span></span><span></span>
                </span>
            </button>
        </div>
    </div>
</header>

{{-- ════════════════════════════════════════════════════════════════════════
     FULLSCREEN MOBILE OVERLAY — navy "premium moment" block.
     clip-path wipe + staggered oversized two-tone links. Scroll-locked.
     ════════════════════════════════════════════════════════════════════════ --}}
<div id="kk-menu-overlay"
     class="kk-menu fixed inset-0 z-[60] flex-col bg-primary-900 text-white"
     role="dialog" aria-modal="true" aria-label="Navigatiemenu" hidden>

    <div class="flex items-center justify-between px-5 py-4 sm:px-8">
        <a href="{{ url('/') }}" class="inline-flex items-baseline gap-px text-[1.35rem] font-medium leading-none tracking-[-0.04em]">
            <span class="text-white">Kot</span><span class="text-accent-500">Kompas</span>
        </a>
        <button type="button"
                class="kk-menu-close inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 text-white transition-colors hover:bg-white/10">
            <span class="sr-only">Menu sluiten</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
        </button>
    </div>

    <p class="kk-menu-item mt-2 flex items-center gap-3 px-5 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-white/55 sm:px-8">
        <span class="inline-block h-px w-8 bg-accent-500"></span> Navigatie
    </p>

    <nav class="flex flex-1 flex-col justify-center gap-1 px-5 sm:px-8" aria-label="Mobiele navigatie">
        @php
            $overlayLinks = [
                ['label' => 'Koten',     'href' => '#koten'],
                ['label' => 'FAQ',       'href' => route('faq')],
                ['label' => 'Contact',   'href' => route('contact')],
                ['label' => 'Inloggen',  'href' => url('/dashboard/login')],
            ];
        @endphp
        @foreach ($overlayLinks as $i => $link)
            <a href="{{ $link['href'] }}"
               class="kk-menu-item group flex items-baseline justify-between border-b border-white/10 py-4 text-[clamp(2.25rem,12vw,3.5rem)] font-medium uppercase leading-[0.95] tracking-[-0.04em] text-white/85 transition-colors hover:text-white">
                <span><span class="text-white/80 group-hover:text-white">{{ Str::substr($link['label'], 0, 1) }}</span>{{ Str::substr($link['label'], 1) }}</span>
                <span class="font-mono text-[0.7rem] tracking-normal text-white/35">0{{ $i + 1 }}</span>
            </a>
        @endforeach
    </nav>

    <div class="kk-menu-item px-5 pb-8 pt-4 sm:px-8">
        <a href="{{ url('/dashboard/register') }}"
           class="flex w-full items-center justify-between rounded-2xl bg-accent-500 px-6 py-5 text-base font-medium text-white transition-colors hover:bg-accent-600">
            Verhuur je kot
            <svg class="h-5 w-5" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <p class="mt-4 text-center text-[0.78rem] text-white/50">Geen makelaarskosten · rechtstreeks van de eigenaar</p>
    </div>
</div>
