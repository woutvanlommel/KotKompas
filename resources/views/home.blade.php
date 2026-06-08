<x-layout title="KotKompas — Vind je kot, rechtstreeks van de eigenaar" body-class="bg-canvas text-ink">

    <x-public-nav :over-hero="true" />

    {{-- Hero — auth language: navy + Antwerp photo + scrim --}}
    <section class="relative -mt-[4.5rem] flex min-h-[92svh] items-end overflow-hidden bg-primary-900 pt-[4.5rem] text-white">
        <div class="absolute inset-0 z-0" aria-hidden="true">
            <img src="{{ asset('img/hero-bg.jpg') }}" alt="" class="h-full w-full object-cover" fetchpriority="high">
            <div class="absolute inset-0 bg-linear-to-r from-primary-900 via-primary-900/85 to-primary-900/30"></div>
            <div class="absolute inset-0 bg-linear-to-t from-primary-900 via-transparent to-primary-900/40"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[88rem] px-5 pb-16 sm:px-8 sm:pb-20">
            <p class="mb-6 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-white/65">
                <span class="inline-block h-px w-8 bg-accent-500"></span> Studentenhuisvesting · Antwerpen
            </p>

            <h1 data-split class="max-w-[16ch] text-[clamp(2.6rem,7.5vw,6.25rem)] font-medium leading-[0.92] tracking-[-0.04em] text-white">
                Vind je kot, rechtstreeks van de eigenaar.
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/75" data-reveal>
                Zoek, vergelijk en plan bezichtigingen — zonder makelaarskosten. Of verhuur je eigen kot, rechtstreeks aan studenten.
            </p>

            {{-- Dual-path UX: two clear intents (rent vs let) --}}
            <div class="mt-10 grid max-w-3xl gap-3 sm:grid-cols-2" data-reveal>
                <a href="#koten" class="group flex items-center justify-between gap-4 rounded-lg border border-white/20 bg-white/10 p-5 backdrop-blur-md transition hover:border-white/40 hover:bg-white/15">
                    <span>
                        <span class="text-[0.62rem] font-medium uppercase tracking-[0.16em] text-accent-400">Ik zoek</span>
                        <span class="mt-1 block text-lg font-medium text-white">Een kot vinden</span>
                        <span class="text-sm text-white/65">Zoek & vergelijk beschikbare koten</span>
                    </span>
                    <span class="shrink-0 text-white transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
                <a href="{{ url('/dashboard/register') }}" class="group flex items-center justify-between gap-4 rounded-lg border border-white/20 bg-white/10 p-5 backdrop-blur-md transition hover:border-white/40 hover:bg-white/15">
                    <span>
                        <span class="text-[0.62rem] font-medium uppercase tracking-[0.16em] text-accent-400">Ik verhuur</span>
                        <span class="mt-1 block text-lg font-medium text-white">Mijn kot plaatsen</span>
                        <span class="text-sm text-white/65">Gratis, zonder makelaarskosten</span>
                    </span>
                    <span class="shrink-0 text-white transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Featured koten — light, scannable (UX) --}}
    <section id="koten" class="scroll-mt-24 py-20 sm:py-28">
        <div class="mx-auto w-full max-w-[88rem] px-5 sm:px-8">
            <div class="mb-12 flex items-end justify-between gap-6">
                <div data-reveal>
                    <p class="mb-3 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ink-soft">
                        <span class="inline-block h-px w-8 bg-accent-500"></span> Beschikbaar nu
                    </p>
                    <h2 class="text-[clamp(1.9rem,4vw,3.25rem)] font-medium leading-[0.95] tracking-[-0.03em] text-ink">Uitgelichte koten</h2>
                </div>
                <a href="{{ route('contact') }}" class="group hidden shrink-0 items-center gap-2 text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink sm:inline-flex">
                    Niets gevonden? Contacteer ons
                    <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>

            @if ($featuredRooms->isNotEmpty())
                <div class="grid grid-cols-1 gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-reveal-stagger>
                    @foreach ($featuredRooms as $room)
                        <x-koten-card :room="$room" />
                    @endforeach
                </div>
            @else
                <div class="border-y border-hairline py-20 text-center" data-reveal>
                    <p class="text-lg font-medium text-ink">Binnenkort beschikbaar</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-soft">Er staan nog geen koten online. Kom snel terug of <a href="{{ route('contact') }}" class="border-b border-ink/40 text-ink transition hover:border-accent-500">neem contact op</a>.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Hoe werkt het --}}
    <section class="border-t border-hairline py-20 sm:py-28">
        <div class="mx-auto w-full max-w-[88rem] px-5 sm:px-8">
            <h2 class="mb-12 text-[clamp(1.9rem,4vw,3.25rem)] font-medium leading-[0.95] tracking-[-0.03em] text-ink" data-reveal>Hoe werkt het?</h2>
            <div data-reveal-stagger>
                @php
                    $steps = [
                        ['Zoek', 'Doorzoek beschikbare koten in jouw studentenstad en vergelijk op prijs, oppervlakte en type.'],
                        ['Plan', 'Vraag rechtstreeks een bezichtiging aan bij de eigenaar — geen tussenpersoon, geen wachttijd.'],
                        ['Huur', 'Regel de huur transparant, zonder makelaarskosten of verborgen kosten.'],
                    ];
                @endphp
                @foreach ($steps as $i => $step)
                    <div class="grid grid-cols-[auto_1fr] gap-6 border-t border-hairline py-7 last:border-b sm:grid-cols-[6rem_1fr_2fr] sm:gap-10">
                        <span class="font-mono text-sm text-accent-500">0{{ $i + 1 }}</span>
                        <h3 class="text-xl font-medium tracking-tight text-ink sm:text-2xl">{{ $step[0] }}</h3>
                        <p class="col-span-2 max-w-xl text-ink-soft sm:col-span-1">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ teaser --}}
    @if ($faqCategories->isNotEmpty())
        <section class="border-t border-hairline py-20 sm:py-28">
            <div class="mx-auto w-full max-w-3xl px-5 sm:px-8">
                <div class="mb-10 flex items-end justify-between gap-6">
                    <h2 class="text-[clamp(1.9rem,4vw,3.25rem)] font-medium leading-[0.95] tracking-[-0.03em] text-ink" data-reveal>Vragen?</h2>
                    <a href="{{ route('faq') }}" class="group shrink-0 text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink">Alle vragen <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span></a>
                </div>
                <div data-reveal>
                    @foreach ($faqCategories as $category)
                        @foreach ($category->faqs as $faq)
                            <details class="group border-t border-hairline last:border-b">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left [&::-webkit-details-marker]:hidden">
                                    <span class="font-medium tracking-tight text-ink">{{ $faq->vraag }}</span>
                                    <span class="text-accent-500 transition-transform duration-300 group-open:rotate-45">+</span>
                                </summary>
                                <div class="pb-5"><p class="max-w-2xl leading-relaxed text-ink-soft">{{ $faq->antwoord }}</p></div>
                            </details>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA verhuurders — navy band, carries the auth pill CTA --}}
    <section class="relative overflow-hidden bg-primary-900 py-20 text-white sm:py-28">
        <div class="relative z-10 mx-auto w-full max-w-[88rem] px-5 sm:px-8">
            <p class="mb-6 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-white/60" data-reveal>
                <span class="inline-block h-px w-8 bg-accent-500"></span> Voor verhuurders
            </p>
            <h2 data-reveal class="max-w-4xl text-[clamp(2.1rem,5.5vw,4.5rem)] font-medium leading-[0.92] tracking-[-0.04em] text-white">
                Verhuur je kot zonder makelaarskosten.
            </h2>
            <a href="{{ url('/dashboard/register') }}" data-reveal class="kk-cta mt-10">
                Word verhuurder
                <span class="kk-cta-chip" aria-hidden="true">
                    <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </a>
        </div>
    </section>

    <x-footer />
</x-layout>
