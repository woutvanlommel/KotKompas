<x-layout title="KotKompas — Vind je kot, rechtstreeks van de eigenaar" body-class="bg-canvas text-ink">

    <x-public-nav :over-hero="true" />

    {{-- ════════════════════════════════════════════════════════════════
         DE OPENING — auth canvas extended: navy photographic, corner meta,
         monumental tight type w/ serif accent, type-forward underline search.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="kk-hero relative isolate flex min-h-[94svh] flex-col justify-end overflow-hidden bg-primary-900 px-5 pb-14 pt-32 sm:px-8 sm:pb-20">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="{{ asset('img/hero-bg.jpg') }}" alt="Studentenstad in Vlaanderen" class="h-[112%] w-full object-cover" fetchpriority="high" data-parallax>
            <div class="absolute inset-x-0 top-0 h-44 bg-linear-to-b from-primary-900/70 to-transparent"></div>
            <div class="absolute inset-0 bg-linear-to-t from-primary-900/95 via-primary-900/55 to-primary-900/10"></div>
        </div>

        {{-- corner meta block (auth device) --}}
        <div class="absolute right-5 top-28 z-10 hidden text-right sm:right-8 sm:top-32 sm:block" data-reveal>
            <p class="text-[0.625rem] font-medium uppercase leading-[1.9] tracking-[0.16em] text-white/55">
                Vlaanderen<br>Studenten — eigenaars<br>Zonder makelaar
            </p>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[88rem]">
            <p class="mb-7 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-white/60" data-reveal>
                <span class="inline-block h-px w-9 bg-accent-500"></span> Studentenhuisvesting
            </p>

            <h1 class="max-w-[15ch] text-[clamp(2.8rem,8vw,7rem)] font-medium leading-[0.85] tracking-[-0.05em] text-balance text-white" data-split>
                <span class="whitespace-nowrap">Vind jouw <span class="kk-serif-it font-normal">kot</span>,</span> rechtstreeks van de eigenaar.
            </h1>

            {{-- Frosted search panel — the auth login-form panel, carried onto the hero --}}
            <form method="GET" action="{{ route('rooms.index') }}" class="kk-glass mt-11 max-w-2xl p-5 sm:p-6" data-reveal>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                    <label class="min-w-0 flex-1" data-suggest-anchor>
                        <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-white/55">Waar zoek je?</span>
                        <input type="text" name="q" placeholder="Stad of buurt…" aria-label="Zoek op stad of buurt" class="kk-uline"
                               data-suggest data-suggest-theme="dark" data-suggest-url="{{ route('rooms.suggestions') }}">
                    </label>
                    <button type="submit" data-magnetic="0.25" class="kk-cta kk-cta--ink mb-1 shrink-0" aria-label="Zoek koten">
                        Zoek
                        <span class="kk-cta-chip" aria-hidden="true">
                            <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </button>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/15 pt-4 text-[0.8rem] text-white/60">
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> Geen makelaarskosten</span>
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> All-in prijzen</span>
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> GDPR-veilig</span>
                </div>
            </form>

            <p class="mt-5 text-[0.8rem] text-white/55" data-reveal>
                Zelf verhuren? <a href="#verhuren" class="font-medium text-white underline-offset-4 transition hover:text-secondary-300 hover:underline">Verhuur je kot →</a>
            </p>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 01 — DE STUDENT. A horizontal step-journey: zoek → huur.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden py-24 sm:py-32">
        <div class="mx-auto w-full max-w-[88rem] px-5 sm:px-8">
            <div class="flex flex-col gap-x-12 gap-y-8 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="mb-6 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                        <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 01 — Voor studenten
                    </p>
                    <h2 data-split class="max-w-[16ch] text-[clamp(2.2rem,5.5vw,4.75rem)] font-medium leading-[0.88] tracking-[-0.05em] text-balance text-ink">
                        Jouw zoektocht, stap voor stap.
                    </h2>
                </div>
                <a href="#koten" data-magnetic="0.2" class="kk-cta kk-cta--ink shrink-0" data-reveal>
                    Zoek een kot
                    <span class="kk-cta-chip" aria-hidden="true">
                        <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>
            <p class="mt-7 max-w-[55ch] text-lg leading-relaxed text-pretty text-ink-soft" data-reveal>
                Een kot zoeken hoort geen gevecht te zijn met makelaars, wachtlijsten en verborgen kosten. Bij KotKompas zoek je zelf, praat je rechtstreeks met de eigenaar en weet je vooraf precies wat je betaalt.
            </p>

            {{-- The journey, horizontal: each step is a beat connected to the next --}}
            <div class="mt-16 grid gap-x-8 gap-y-12 border-t border-hairline pt-12 sm:grid-cols-2 lg:grid-cols-4" data-reveal-stagger>
                @php
                    $reis = [
                        ['Zoek', 'Doorzoek koten in jouw studentenstad en filter op prijs, oppervlakte en type.'],
                        ['Contacteer', 'Praat rechtstreeks met de eigenaar — geen tussenpersoon, geen wachttijd.'],
                        ['Bezichtig', 'Plan een bezichtiging wanneer het jou past.'],
                        ['Huur', 'Regel alles transparant. Eén all-in prijs, geen verrassingen achteraf.'],
                    ];
                @endphp
                @foreach ($reis as $i => $beat)
                    <div class="group relative">
                        <span class="kk-num block text-5xl leading-none text-ink/15 transition-colors duration-300 group-hover:text-accent-500 sm:text-6xl">0{{ $i + 1 }}</span>
                        <h3 class="mt-5 text-xl font-medium tracking-tight text-ink">{{ $beat[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $beat[1] }}</p>
                        @unless ($loop->last)
                            <span class="pointer-events-none absolute -right-4 top-4 hidden text-2xl text-ink-soft/30 lg:block" aria-hidden="true">→</span>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 02 — DE EIGENAAR. Dark poster: the giant word dominates, photo beside.
         ════════════════════════════════════════════════════════════════ --}}
    <section id="verhuren" class="scroll-mt-24 px-5 pb-8 sm:px-8">
        <div class="mx-auto w-full max-w-[88rem]">
            <div class="relative grid overflow-hidden rounded-[2rem] bg-primary-900 text-white lg:grid-cols-[1.3fr_0.7fr]">
                {{-- The poster --}}
                <div class="relative z-10 px-6 py-16 sm:px-14 sm:py-24">
                    <p class="mb-9 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-white/55" data-reveal>
                        <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 02 — Voor eigenaars
                    </p>
                    <a href="{{ url('/dashboard/register') }}" data-magnetic="0.12" class="kk-bigword" data-reveal>
                        <span class="kk-bigword-word">Verhuur je kot</span>
                        <svg class="kk-bigword-arrow" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 12L12 4M12 4H6M12 4V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <p class="mt-9 max-w-md text-lg leading-relaxed text-pretty text-white/70" data-reveal>
                        Zet je kot in enkele minuten online en bereik studenten die écht op zoek zijn. Jij kiest je huurder — wij vragen geen commissie.
                    </p>
                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 border-t border-white/15 pt-6 text-sm text-white/65" data-reveal-stagger>
                        <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> Gratis online plaatsen</span>
                        <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> Studenten rechtstreeks</span>
                        <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> Jij kiest je huurder</span>
                    </div>
                </div>

                {{-- Photo panel, fading into the navy at the seam --}}
                <div class="relative min-h-[15rem] overflow-hidden">
                    <img src="{{ asset('img/hero-bg.jpg') }}" alt="" class="absolute inset-0 h-[112%] w-full object-cover" loading="lazy" data-parallax>
                    <div class="absolute inset-0 bg-linear-to-t from-primary-900 via-primary-900/40 to-primary-900/10 lg:bg-linear-to-l lg:from-transparent lg:via-primary-900/30 lg:to-primary-900"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 03 — SAMEN. Twee stemmen: een huurder + een verhuurder.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="px-5 py-24 sm:px-8 sm:py-32">
        <div class="mx-auto w-full max-w-[88rem]">
            <div class="mb-14 max-w-[22ch]">
                <p class="mb-6 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                    <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 03 — Samen
                </p>
                <h2 data-split class="text-[clamp(2.2rem,5.5vw,4.5rem)] font-medium leading-[0.9] tracking-[-0.05em] text-balance text-ink">
                    Twee kanten, één plek.
                </h2>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Huurder --}}
                <figure data-converge="left" class="flex h-full flex-col justify-between rounded-[2rem] border border-hairline bg-canvas-deep p-8 sm:p-12">
                    <div>
                        <span class="text-[0.625rem] font-medium uppercase tracking-[0.16em] text-accent-500">Huurder</span>
                        <blockquote class="mt-6 text-balance text-[clamp(1.35rem,2.6vw,2.1rem)] font-medium leading-[1.18] tracking-[-0.02em] text-ink">
                            “Binnen twee dagen had ik mijn kot. Rechtstreeks met de eigenaar — geen makelaar, geen verborgen kosten.”
                        </blockquote>
                    </div>
                    <figcaption class="mt-10 flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-primary-900 text-base font-medium text-white">L</span>
                        <div>
                            <p class="text-sm font-medium tracking-tight text-ink">Lotte</p>
                            <p class="text-[0.8rem] text-ink-soft">Studente · Gent</p>
                        </div>
                    </figcaption>
                </figure>

                {{-- Verhuurder --}}
                <figure data-converge="right" class="flex h-full flex-col justify-between rounded-[2rem] bg-primary-900 p-8 text-white sm:p-12">
                    <div>
                        <span class="text-[0.625rem] font-medium uppercase tracking-[0.16em] text-accent-400">Verhuurder</span>
                        <blockquote class="mt-6 text-balance text-[clamp(1.35rem,2.6vw,2.1rem)] font-medium leading-[1.18] tracking-[-0.02em] text-white">
                            “Mijn kot stond in vijf minuten online. Ik koos zelf mijn huurder — zonder commissie of tussenpersoon.”
                        </blockquote>
                    </div>
                    <figcaption class="mt-10 flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-white text-base font-medium text-primary-900">J</span>
                        <div>
                            <p class="text-sm font-medium tracking-tight text-white">Jan</p>
                            <p class="text-[0.8rem] text-white/60">Verhuurder · Antwerpen</p>
                        </div>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HET BEWIJS — echte koten.
         ════════════════════════════════════════════════════════════════ --}}
    <section id="koten" class="scroll-mt-24 border-t border-hairline py-20 sm:py-24">
        <div class="mx-auto w-full max-w-[88rem] px-5 sm:px-8">
            <div class="mb-12 flex items-end justify-between gap-6">
                <div>
                    <p class="mb-3 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                        <span class="inline-block h-px w-9 bg-accent-500"></span> Nu beschikbaar
                    </p>
                    <h2 data-split class="text-[clamp(2rem,5vw,3.75rem)] font-medium leading-[0.9] tracking-[-0.05em] text-balance text-ink">
                        Echte koten, klaar om te <span class="kk-serif-it font-normal text-secondary-600">bezichtigen</span>
                    </h2>
                </div>
                <a href="{{ route('contact') }}" class="group hidden shrink-0 items-center gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft transition hover:text-secondary-600 sm:inline-flex">
                    Niets gevonden? Contacteer ons
                    <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>

            @if ($featuredRooms->isNotEmpty())
                <div class="grid grid-cols-1 gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-4" data-reveal-stagger>
                    @foreach ($featuredRooms as $room)
                        <x-koten-card :room="$room" />
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-hairline bg-white py-20 text-center" data-reveal>
                    <p class="text-lg font-medium text-ink">Binnenkort beschikbaar</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-soft">Er staan nog geen koten online. Kom snel terug of <a href="{{ route('contact') }}" class="border-b border-ink/40 text-ink transition hover:border-secondary-500">neem contact op</a>.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         NOG VRAGEN — FAQ.
         ════════════════════════════════════════════════════════════════ --}}
    @if ($faqCategories->isNotEmpty())
        <section class="border-t border-hairline py-20 sm:py-28">
            <div class="mx-auto grid w-full max-w-[88rem] gap-x-16 gap-y-10 px-5 sm:px-8 lg:grid-cols-[0.7fr_1fr]">
                <div class="lg:sticky lg:top-28 lg:self-start">
                    <p class="mb-3 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                        <span class="inline-block h-px w-9 bg-accent-500"></span> Nog vragen?
                    </p>
                    <h2 data-split class="text-[clamp(2rem,5vw,3.75rem)] font-medium leading-[0.9] tracking-[-0.05em] text-balance text-ink">
                        Alles wat je wil <span class="kk-serif-it font-normal text-secondary-600">weten</span>
                    </h2>
                    <div class="mt-6 border-t border-hairline pt-5" data-reveal>
                        <p class="text-sm leading-relaxed text-ink-soft">Geen antwoord op je vraag gevonden?</p>
                        <a href="{{ route('contact') }}" class="group mt-3 inline-flex items-center gap-2 text-sm font-medium text-ink transition hover:text-secondary-600">
                            Contacteer ons <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                        </a>
                    </div>
                </div>
                <div data-reveal>
                    @foreach ($faqCategories as $category)
                        @foreach ($category->faqs as $faq)
                            <details class="group border-t border-hairline last:border-b">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left [&::-webkit-details-marker]:hidden">
                                    <span class="font-medium tracking-tight text-ink transition-colors group-hover:text-secondary-600">{{ $faq->vraag }}</span>
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border border-hairline text-accent-500 transition-transform duration-300 group-open:rotate-45">+</span>
                                </summary>
                                <div class="pb-5"><p class="max-w-2xl leading-relaxed text-ink-soft">{{ $faq->antwoord }}</p></div>
                            </details>
                        @endforeach
                    @endforeach
                    <a href="{{ route('faq') }}" class="group mt-8 inline-flex items-center gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft transition hover:text-secondary-600">Alle vragen <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span></a>
                </div>
            </div>
        </section>
    @endif

    <x-footer />
</x-layout>
