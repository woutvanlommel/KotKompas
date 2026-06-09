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
            <div class="kk-glass mt-11 max-w-2xl p-5 sm:p-6" data-reveal>
                <div class="flex items-end gap-5">
                    <label class="min-w-0 flex-1">
                        <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-white/55">Waar zoek je?</span>
                        <input type="text" name="q" placeholder="Stad of buurt…" aria-label="Zoek op stad of buurt" class="kk-uline">
                    </label>
                    <a href="#koten" data-magnetic="0.25" class="kk-cta kk-cta--ink mb-1 shrink-0" aria-label="Zoek koten">
                        Zoek
                        <span class="kk-cta-chip" aria-hidden="true">
                            <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/15 pt-4 text-[0.8rem] text-white/60">
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> Geen makelaarskosten</span>
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> All-in prijzen</span>
                    <span class="flex items-center gap-2.5"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span> GDPR-veilig</span>
                </div>
            </div>

            <p class="mt-5 text-[0.8rem] text-white/55" data-reveal>
                Zelf verhuren? <a href="#verhuren" class="font-medium text-white underline-offset-4 transition hover:text-secondary-300 hover:underline">Verhuur je kot →</a>
            </p>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 01 — DE STUDENT. Light, editorial-asymmetric, serif numeral.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden py-24 sm:py-32">
        <span class="kk-num pointer-events-none absolute -top-8 right-2 z-0 select-none text-[clamp(8rem,22vw,20rem)] text-ink/[0.05] sm:right-10">01</span>
        <div class="relative z-10 mx-auto grid w-full max-w-[88rem] gap-x-16 gap-y-14 px-5 sm:px-8 lg:grid-cols-[1.1fr_0.82fr] lg:items-start">
            <div>
                <p class="mb-6 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                    <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 01 — Voor studenten
                </p>
                <h2 data-split class="max-w-[13ch] text-[clamp(2.2rem,5.5vw,4.75rem)] font-medium leading-[0.88] tracking-[-0.05em] text-balance text-ink">
                    Jij zoekt een plek die voelt als <span class="kk-serif-it font-normal text-secondary-600">thuis</span>.
                </h2>
                <p class="mt-7 max-w-[46ch] text-lg leading-relaxed text-pretty text-ink-soft" data-reveal>
                    Een kot zoeken hoort geen gevecht te zijn met makelaars, wachtlijsten en verborgen kosten. Bij KotKompas zoek je zelf, praat je rechtstreeks met de eigenaar en weet je vooraf precies wat je betaalt.
                </p>

                <div class="mt-12" data-reveal-stagger>
                    @php
                        $reis = [
                            ['Zoek', 'Doorzoek koten in jouw studentenstad en filter op prijs, oppervlakte en type.'],
                            ['Contacteer', 'Praat rechtstreeks met de eigenaar — geen tussenpersoon, geen wachttijd.'],
                            ['Bezichtig', 'Plan een bezichtiging wanneer het jou past.'],
                            ['Huur', 'Regel alles transparant. Eén all-in prijs, geen verrassingen achteraf.'],
                        ];
                    @endphp
                    @foreach ($reis as $i => $beat)
                        <div class="group flex items-baseline gap-6 border-t border-hairline py-5 last:border-b">
                            <span class="kk-num text-3xl text-ink/65 transition-colors group-hover:text-secondary-600 sm:text-4xl">0{{ $i + 1 }}</span>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium tracking-tight text-ink transition-colors group-hover:text-secondary-600">{{ $beat[0] }}</h3>
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
                <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-primary-900/85 via-primary-900/20 to-transparent p-6">
                    <p class="text-[0.625rem] font-medium uppercase tracking-[0.16em] text-white/70">Jouw volgende thuis</p>
                    <p class="mt-1.5 text-lg font-medium leading-snug text-white">Rechtstreeks van de eigenaar — geen makelaar ertussen.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         HOOFDSTUK 02 — DE EIGENAAR. Navy auth panel, giant switch-word.
         ════════════════════════════════════════════════════════════════ --}}
    <section id="verhuren" class="scroll-mt-24 px-5 pb-8 sm:px-8">
        <div class="mx-auto w-full max-w-[88rem]">
            <div class="relative overflow-hidden rounded-[2rem] bg-primary-900 px-6 py-20 text-white sm:px-14 sm:py-28">
                <span class="kk-num pointer-events-none absolute -top-10 right-4 z-0 select-none text-[clamp(8rem,20vw,18rem)] text-white/[0.06] sm:right-10">02</span>
                <div class="absolute inset-0 z-0 opacity-20" aria-hidden="true">
                    <img src="{{ asset('img/hero-bg.jpg') }}" alt="" class="h-[120%] w-full object-cover" data-parallax>
                    <div class="absolute inset-0 bg-linear-to-bl from-primary-900 via-primary-900/85 to-primary-900/40"></div>
                </div>

                <div class="relative z-10 grid gap-x-16 gap-y-12 lg:grid-cols-[0.9fr_1.05fr] lg:items-center">
                    <ul class="order-2 lg:order-1" data-reveal-stagger>
                        @php
                            $voordelen = [
                                ['Gratis online', 'Plaats je kot zonder kosten, commissie of abonnement.'],
                                ['Rechtstreeks bereik', 'Studenten contacteren jou direct — geen makelaar ertussen.'],
                                ['Jij beslist', 'Kies zelf wie er in jouw kot komt wonen.'],
                            ];
                        @endphp
                        @foreach ($voordelen as $i => $v)
                            <li class="group flex items-baseline gap-6 border-t border-white/15 py-5 last:border-b">
                                <span class="kk-num text-3xl text-white/45 transition-colors group-hover:text-secondary-300 sm:text-4xl">0{{ $i + 1 }}</span>
                                <div>
                                    <h3 class="text-lg font-medium tracking-tight text-white">{{ $v[0] }}</h3>
                                    <p class="mt-1 max-w-sm text-sm leading-relaxed text-white/65">{{ $v[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="order-1 lg:order-2">
                        <p class="mb-6 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-white/55" data-reveal>
                            <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 02 — Voor eigenaars
                        </p>
                        <h2 data-split class="max-w-[15ch] text-[clamp(2.2rem,5.5vw,4.5rem)] font-medium leading-[0.88] tracking-[-0.05em] text-balance text-white">
                            Jij hebt een kot. Wij brengen de <span class="kk-serif-it font-normal">student</span>.
                        </h2>
                        <p class="mt-7 max-w-md text-lg leading-relaxed text-pretty text-white/70" data-reveal>
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
         HOOFDSTUK 03 — SAMEN. Light climax, monumental statement.
         ════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden py-24 sm:py-32">
        <span class="kk-num pointer-events-none absolute -top-8 left-2 z-0 select-none text-[clamp(8rem,22vw,20rem)] text-ink/[0.05] sm:left-10">03</span>
        <div class="relative z-10 mx-auto w-full max-w-[80rem] px-5 text-center sm:px-8">
            <p class="mb-10 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink-soft" data-reveal>
                <span class="inline-block h-px w-10 bg-accent-500"></span> Hoofdstuk 03 — Samen
            </p>

            <div class="mb-12 flex items-center justify-center gap-5 text-sm font-medium tracking-tight sm:gap-9">
                <span data-converge="left" class="text-ink/70">De student</span>
                <span class="relative flex h-2.5 w-2.5 shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-500 opacity-60 motion-reduce:animate-none"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-accent-500"></span>
                </span>
                <span data-converge="right" class="text-ink/70">De eigenaar</span>
            </div>

            <h2 data-split class="mx-auto max-w-[18ch] text-[clamp(2.4rem,6.5vw,5.5rem)] font-medium leading-[0.86] tracking-[-0.05em] text-balance text-ink">
                Rechtstreeks met elkaar. Geen makelaar <span class="kk-serif-it font-normal text-secondary-600">ertussen</span>.
            </h2>
            <p class="mx-auto mt-7 max-w-[52ch] text-lg leading-relaxed text-pretty text-ink-soft" data-reveal>
                Dat is het hele idee. Twee mensen die elkaar vinden zonder tussenpersoon — eerlijk, transparant en veilig.
            </p>

            <dl class="mx-auto mt-16 grid max-w-4xl gap-x-12 gap-y-8 text-left sm:grid-cols-3" data-reveal-stagger>
                @php
                    $waarom = [
                        ['Geen makelaarskosten', 'Geen tussenpersoon, geen commissie.'],
                        ['All-in prijstransparantie', 'Eén duidelijke prijs. Geen verrassingen.'],
                        ['GDPR-veilig', 'Jouw gegevens, altijd beschermd.'],
                    ];
                @endphp
                @foreach ($waarom as $i => $w)
                    <div class="border-t border-ink/15 pt-5">
                        <dt class="flex items-baseline gap-3">
                            <span class="kk-num text-xl text-accent-500">0{{ $i + 1 }}</span>
                            <span class="text-base font-medium tracking-tight text-ink">{{ $w[0] }}</span>
                        </dt>
                        <dd class="mt-2 pl-9 text-sm leading-relaxed text-ink-soft">{{ $w[1] }}</dd>
                    </div>
                @endforeach
            </dl>
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
                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3" data-reveal-stagger>
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
