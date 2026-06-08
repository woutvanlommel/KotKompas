<x-layout title="KotKompas — Vind je kot, rechtstreeks van de eigenaar" body-class="bg-canvas text-ink">

    <x-public-nav :over-hero="true" />

    {{-- ════════════════════════════════════════════════════════════════
         DE OPENING — twee kanten, één plek. Student-first, nod naar verhuurder.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="kk-hero relative isolate flex min-h-[92svh] flex-col justify-end overflow-hidden bg-primary-900 px-5 pb-12 pt-32 sm:px-8 sm:pb-16">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="{{ asset('img/hero-bg.jpg') }}" alt="Studentenstad in Vlaanderen" class="h-[112%] w-full object-cover" fetchpriority="high" data-parallax>
            <div class="absolute inset-x-0 top-0 h-44 bg-linear-to-b from-ink/55 to-transparent"></div>
            <div class="absolute inset-0 bg-linear-to-t from-ink/90 via-ink/45 to-ink/5"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[88rem]">
            <p class="mb-6 inline-flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-white/70" data-reveal>
                <span class="inline-block h-px w-8 bg-accent-500"></span> Studenten &amp; eigenaars · rechtstreeks
            </p>

            <h1 class="max-w-[18ch] text-[clamp(2.5rem,6.2vw,5.25rem)] font-medium leading-[0.98] tracking-[-0.04em] text-white" data-split>
                Vind jouw <span class="text-accent-500">kot</span>, rechtstreeks van de eigenaar.
            </h1>

            <div class="mt-9 flex max-w-2xl items-center gap-2 rounded-2xl bg-white p-2 shadow-[0_30px_70px_-24px_rgba(0,0,0,0.6)]" data-reveal>
                <svg class="ml-2.5 h-5 w-5 shrink-0 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/>
                </svg>
                <input type="text" name="q" placeholder="Zoek op stad of buurt…" aria-label="Zoek op stad of buurt"
                       class="min-w-0 flex-1 bg-transparent px-1 py-2.5 text-ink placeholder:text-ink-soft focus:outline-none">
                <a href="#koten" data-magnetic="0.25" class="kk-cta kk-cta--ink shrink-0" aria-label="Zoek koten">
                    Zoek
                    <span class="kk-cta-chip" aria-hidden="true">
                        <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2.5" data-reveal>
                <span class="flex flex-wrap items-center gap-2.5">
                    @foreach (['Geen makelaarskosten', 'All-in prijzen', 'GDPR-veilig'] as $chip)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-sm text-white backdrop-blur-sm">
                            <svg class="h-3.5 w-3.5 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $chip }}
                        </span>
                    @endforeach
                </span>
                <span class="text-sm text-white/55">
                    Zelf een kot te verhuur?
                    <a href="#verhuren" class="ml-1 font-medium text-white underline-offset-4 transition hover:text-accent-500 hover:underline">Verhuur je kot →</a>
                </span>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 01 — DE STUDENT. Licht, links. Jouw zoektocht als reis.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden py-24 sm:py-32">
        <div class="mx-auto grid w-full max-w-[88rem] gap-x-16 gap-y-14 px-5 sm:px-8 lg:grid-cols-[1.05fr_0.9fr] lg:items-start">
            <div>
                <p class="mb-5 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-ink-soft" data-reveal>
                    <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 01 — Voor studenten
                </p>
                <h2 data-split class="max-w-[14ch] text-[clamp(2.1rem,5vw,4rem)] font-medium leading-[0.98] tracking-[-0.035em] text-ink">
                    Jij zoekt een plek die voelt als <span class="text-accent-500">thuis</span>.
                </h2>
                <p class="mt-7 max-w-xl text-lg leading-relaxed text-ink-soft" data-reveal>
                    Een kot zoeken hoort geen gevecht te zijn met makelaars, wachtlijsten en verborgen kosten. Bij KotKompas zoek je zelf, praat je rechtstreeks met de eigenaar en weet je vooraf precies wat je betaalt.
                </p>

                <div class="mt-12 space-y-px" data-reveal-stagger>
                    @php
                        $reis = [
                            ['Zoek', 'Doorzoek koten in jouw studentenstad en filter op prijs, oppervlakte en type.'],
                            ['Contacteer', 'Praat rechtstreeks met de eigenaar — geen tussenpersoon, geen wachttijd.'],
                            ['Bezichtig', 'Plan een bezichtiging wanneer het jou past.'],
                            ['Huur', 'Regel alles transparant. Eén all-in prijs, geen verrassingen achteraf.'],
                        ];
                    @endphp
                    @foreach ($reis as $i => $beat)
                        <div class="group flex items-baseline gap-6 border-t border-hairline py-5 transition-colors last:border-b hover:bg-canvas-deep/40">
                            <span class="font-mono text-xl text-accent-500 sm:text-2xl">0{{ $i + 1 }}</span>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium tracking-tight text-ink">{{ $beat[0] }}</h3>
                                <p class="mt-1 max-w-md text-sm leading-relaxed text-ink-soft">{{ $beat[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="#koten" data-magnetic="0.2" class="kk-cta kk-cta--ink mt-10" data-reveal>
                    Zoek een kot
                    <span class="kk-cta-chip" aria-hidden="true">
                        <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>

            <div class="relative aspect-[4/5] overflow-hidden rounded-[1.75rem] bg-canvas-deep lg:sticky lg:top-28" data-reveal>
                <img src="{{ asset('img/hero-test.jpg') }}" alt="Studentenkamer" class="h-[112%] w-full object-cover" loading="lazy" data-parallax>
                <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-ink/80 via-ink/20 to-transparent p-6">
                    <p class="text-[0.7rem] font-medium uppercase tracking-[0.16em] text-white/70">Jouw volgende thuis</p>
                    <p class="mt-1.5 text-lg font-medium text-white">Rechtstreeks van de eigenaar — geen makelaar ertussen.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 02 — DE EIGENAAR. Navy, rechts. Jouw kot verhuren.
         ════════════════════════════════════════════════════════════════ --}}
    <section id="verhuren" class="scroll-mt-24 px-5 pb-8 sm:px-8">
        <div class="mx-auto w-full max-w-[88rem]">
            <div class="relative overflow-hidden rounded-[2rem] bg-primary-900 px-6 py-20 text-white sm:px-14 sm:py-28">
                <div class="absolute inset-0 z-0 opacity-25" aria-hidden="true">
                    <img src="{{ asset('img/hero-bg.jpg') }}" alt="" class="h-[120%] w-full object-cover" data-parallax>
                    <div class="absolute inset-0 bg-linear-to-bl from-primary-900 via-primary-900/85 to-primary-900/45"></div>
                </div>

                <div class="relative z-10 grid gap-x-16 gap-y-12 lg:grid-cols-[0.9fr_1.05fr] lg:items-center">
                    <ul class="order-2 space-y-px lg:order-1" data-reveal-stagger>
                        @php
                            $voordelen = [
                                ['Gratis online', 'Plaats je kot zonder kosten, commissie of abonnement.'],
                                ['Rechtstreeks bereik', 'Studenten contacteren jou direct — geen makelaar ertussen.'],
                                ['Jij beslist', 'Kies zelf wie er in jouw kot komt wonen.'],
                            ];
                        @endphp
                        @foreach ($voordelen as $i => $v)
                            <li class="flex items-baseline gap-6 border-t border-white/15 py-5 last:border-b">
                                <span class="font-mono text-xl text-accent-400 sm:text-2xl">0{{ $i + 1 }}</span>
                                <div>
                                    <h3 class="text-lg font-medium tracking-tight text-white">{{ $v[0] }}</h3>
                                    <p class="mt-1 max-w-sm text-sm leading-relaxed text-white/65">{{ $v[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="order-1 lg:order-2">
                        <p class="mb-5 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-white/55" data-reveal>
                            <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 02 — Voor eigenaars
                        </p>
                        <h2 data-split class="max-w-[16ch] text-[clamp(2.1rem,5vw,4rem)] font-medium leading-[0.98] tracking-[-0.035em] text-white">
                            Jij hebt een kot. Wij brengen de student.
                        </h2>
                        <p class="mt-7 max-w-md text-lg leading-relaxed text-white/70" data-reveal>
                            Zet je kot in enkele minuten online en bereik studenten die écht op zoek zijn. Jij kiest je huurder — wij vragen geen commissie.
                        </p>
                        <a href="{{ url('/dashboard/register') }}" data-magnetic="0.15" class="kk-bigword mt-10" data-reveal>
                            <span class="kk-bigword-word">Verhuur je kot</span>
                            <svg class="kk-bigword-arrow" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 12L12 4M12 4H6M12 4V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 03 — SAMEN. De climax: twee kanten komen samen.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden py-24 text-center sm:py-32">
        <div class="mx-auto w-full max-w-[72rem] px-5 sm:px-8">
            <p class="mb-10 inline-flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.2em] text-ink-soft" data-reveal>
                <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 03 — Samen
            </p>

            {{-- De convergentie: student ⟶ ● ⟵ eigenaar --}}
            <div class="mb-12 flex items-center justify-center gap-4 sm:gap-8">
                <span data-converge="left" class="inline-flex items-center gap-2 rounded-full border border-hairline bg-white px-4 py-2 text-sm font-medium tracking-tight text-ink shadow-sm">
                    De student <span class="text-accent-500">→</span>
                </span>
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-500 opacity-60 motion-reduce:animate-none"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-accent-500"></span>
                </span>
                <span data-converge="right" class="inline-flex items-center gap-2 rounded-full border border-hairline bg-primary-900 px-4 py-2 text-sm font-medium tracking-tight text-white shadow-sm">
                    <span class="text-accent-500">←</span> De eigenaar
                </span>
            </div>

            <h2 data-split class="mx-auto max-w-[20ch] text-[clamp(2.3rem,6vw,5rem)] font-medium leading-[0.95] tracking-[-0.04em] text-ink">
                Rechtstreeks met elkaar. <span class="text-accent-500">Geen makelaar ertussen.</span>
            </h2>
            <p class="mx-auto mt-7 max-w-2xl text-lg leading-relaxed text-ink-soft" data-reveal>
                Dat is het hele idee. Twee mensen die elkaar vinden zonder tussenpersoon — eerlijk, transparant en veilig.
            </p>

            <div class="mt-16 grid gap-px overflow-hidden rounded-2xl border border-hairline text-left sm:grid-cols-3" data-reveal-stagger>
                @php
                    $waarom = [
                        ['Geen makelaarskosten', 'Geen tussenpersoon, geen commissie. Wat je ziet, betaal je.'],
                        ['All-in prijstransparantie', 'Eén duidelijke prijs. Geen verborgen kosten achteraf.'],
                        ['GDPR-veilig', 'Jouw gegevens blijven beschermd en worden nooit doorverkocht.'],
                    ];
                @endphp
                @foreach ($waarom as $i => $w)
                    <div class="bg-canvas-deep/50 p-7">
                        <span class="font-mono text-sm text-accent-500">0{{ $i + 1 }}</span>
                        <p class="mt-4 text-base font-medium tracking-tight text-ink">{{ $w[0] }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $w[1] }}</p>
                    </div>
                @endforeach
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
                    <p class="mb-3 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ink-soft" data-reveal>
                        <span class="inline-block h-px w-8 bg-accent-500"></span> Nu beschikbaar
                    </p>
                    <h2 data-split class="text-[clamp(1.9rem,4.5vw,3.5rem)] font-medium leading-[0.95] tracking-[-0.03em] text-ink">Echte koten, klaar om te bezichtigen</h2>
                </div>
                <a href="{{ route('contact') }}" class="group hidden shrink-0 items-center gap-2 text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink sm:inline-flex">
                    Niets gevonden? Contacteer ons
                    <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>

            @if ($featuredRooms->isNotEmpty())
                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3" data-reveal-stagger>
                    @foreach ($featuredRooms as $room)
                        <x-koten-card :room="$room" />
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-hairline bg-white py-20 text-center" data-reveal>
                    <p class="text-lg font-medium text-ink">Binnenkort beschikbaar</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-soft">Er staan nog geen koten online. Kom snel terug of <a href="{{ route('contact') }}" class="border-b border-ink/40 text-ink transition hover:border-accent-500">neem contact op</a>.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         NOG VRAGEN — FAQ als laatste geruststelling voor de keuze.
         ════════════════════════════════════════════════════════════════ --}}
    @if ($faqCategories->isNotEmpty())
        <section class="border-t border-hairline py-20 sm:py-28">
            <div class="mx-auto grid w-full max-w-[88rem] gap-x-16 gap-y-10 px-5 sm:px-8 lg:grid-cols-[0.7fr_1fr]">
                <div class="lg:sticky lg:top-28 lg:self-start">
                    <p class="mb-3 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ink-soft" data-reveal>
                        <span class="inline-block h-px w-8 bg-accent-500"></span> Nog vragen?
                    </p>
                    <h2 data-split class="text-[clamp(1.9rem,4.5vw,3.5rem)] font-medium leading-[0.95] tracking-[-0.03em] text-ink">Alles wat je wil weten</h2>
                    <div class="mt-6 rounded-2xl border border-hairline bg-white p-6" data-reveal>
                        <p class="text-sm leading-relaxed text-ink-soft">Geen antwoord op je vraag gevonden?</p>
                        <a href="{{ route('contact') }}" class="group mt-3 inline-flex items-center gap-2 text-sm font-medium text-ink transition hover:text-accent-600">
                            Contacteer ons <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                        </a>
                    </div>
                </div>
                <div data-reveal>
                    @foreach ($faqCategories as $category)
                        @foreach ($category->faqs as $faq)
                            <details class="group border-t border-hairline last:border-b">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left [&::-webkit-details-marker]:hidden">
                                    <span class="font-medium tracking-tight text-ink">{{ $faq->vraag }}</span>
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border border-hairline text-accent-500 transition-transform duration-300 group-open:rotate-45">+</span>
                                </summary>
                                <div class="pb-5"><p class="max-w-2xl leading-relaxed text-ink-soft">{{ $faq->antwoord }}</p></div>
                            </details>
                        @endforeach
                    @endforeach
                    <a href="{{ route('faq') }}" class="group mt-8 inline-flex items-center gap-2 text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink">Alle vragen <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span></a>
                </div>
            </div>
        </section>
    @endif

    <x-footer />
</x-layout>
