<footer class="bg-canvas">
    {{-- ── Premium moment: navy CTA band (big-CTA row, asymmetric) ── --}}
    <section class="bg-primary-900 text-white">
        <div class="mx-auto grid w-full max-w-[88rem] gap-12 px-5 py-20 sm:px-8 lg:grid-cols-[1.55fr_1fr] lg:items-end lg:py-28">
            <div data-reveal>
                <p class="flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-white/55">
                    <span class="inline-block h-px w-8 bg-accent-500"></span> Vind je plek
                </p>
                <h2 class="mt-6 max-w-3xl text-balance text-[clamp(2.6rem,6.5vw,5.5rem)] font-medium leading-[0.9] tracking-[-0.04em]" data-split>
                    <span class="text-white">Klaar om je</span><br>
                    <span class="text-white">kot te </span><span class="text-accent-500">vinden?</span>
                </h2>
            </div>

            <div class="lg:pb-3" data-reveal>
                <p class="max-w-md text-base leading-relaxed text-white/70">
                    Rechtstreeks van de eigenaar, all-in prijzen, geen makelaarskosten. Bezichtig zonder tussenpersoon.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('home') }}#koten" data-magnetic="0.2" class="kk-cta kk-cta--ink">
                        Bekijk koten
                        <span class="kk-cta-chip">
                            <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                    <a href="{{ url('/dashboard/register') }}" class="text-sm font-medium tracking-tight text-white/70 underline-offset-4 transition hover:text-white hover:underline">
                        of verhuur je kot →
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Sitemap grid (light, asymmetric: brand block + 4 indexed columns) ── --}}
    <div class="mx-auto w-full max-w-[88rem] px-5 sm:px-8">
        <div class="grid gap-12 border-b border-hairline py-16 lg:grid-cols-[1.5fr_repeat(4,minmax(0,1fr))] lg:gap-10 lg:py-20">
            {{-- Brand block --}}
            <div class="lg:max-w-sm" data-reveal>
                <a href="{{ url('/') }}" class="inline-flex items-baseline gap-1 text-2xl font-medium tracking-[-0.03em]" aria-label="KotKompas home">
                    <span class="text-ink">Kot</span><span class="text-accent-500">Kompas</span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-soft">
                    Het kompas voor studentenhuisvesting in Vlaanderen — huur rechtstreeks van de eigenaar, zonder tussenpersoon.
                </p>
                <p class="mt-6 flex items-center gap-2.5 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-accent-500"></span> GDPR-conform · prijstransparantie
                </p>
            </div>

            {{-- Indexed link columns --}}
            <nav class="grid grid-cols-2 gap-10 sm:grid-cols-4 lg:col-span-4" data-reveal-stagger>
                <div>
                    <p class="flex items-baseline gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">
                        <span class="font-mono text-[0.65rem] text-accent-500">01</span> Ontdek
                    </p>
                    <ul class="mt-5 space-y-3 text-sm text-ink">
                        <li><a href="{{ url('/') }}" class="inline-block transition-colors hover:text-accent-600">Home</a></li>
                        <li><a href="{{ route('home') }}#koten" class="inline-block transition-colors hover:text-accent-600">Koten</a></li>
                        <li><a href="{{ route('faq') }}" class="inline-block transition-colors hover:text-accent-600">Veelgestelde vragen</a></li>
                    </ul>
                </div>

                <div>
                    <p class="flex items-baseline gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">
                        <span class="font-mono text-[0.65rem] text-accent-500">02</span> Account
                    </p>
                    <ul class="mt-5 space-y-3 text-sm text-ink">
                        <li><a href="{{ url('/dashboard/login') }}" class="inline-block transition-colors hover:text-accent-600">Inloggen</a></li>
                        <li><a href="{{ url('/dashboard/register') }}" class="inline-block transition-colors hover:text-accent-600">Registreren</a></li>
                        <li><a href="{{ url('/dashboard/register') }}" class="inline-block transition-colors hover:text-accent-600">Verhuur je kot</a></li>
                    </ul>
                </div>

                <div>
                    <p class="flex items-baseline gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">
                        <span class="font-mono text-[0.65rem] text-accent-500">03</span> Juridisch
                    </p>
                    <ul class="mt-5 space-y-3 text-sm text-ink">
                        <li><a href="{{ route('privacy') }}" class="inline-block transition-colors hover:text-accent-600">Privacybeleid</a></li>
                        <li><a href="{{ route('cookies') }}" class="inline-block transition-colors hover:text-accent-600">Cookiebeleid</a></li>
                        <li><a href="{{ route('algemene-voorwaarden') }}" class="inline-block transition-colors hover:text-accent-600">Algemene voorwaarden</a></li>
                    </ul>
                </div>

                <div>
                    <p class="flex items-baseline gap-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">
                        <span class="font-mono text-[0.65rem] text-accent-500">04</span> Contact
                    </p>
                    <ul class="mt-5 space-y-3 text-sm text-ink">
                        <li><a href="{{ route('contact') }}" class="inline-block transition-colors hover:text-accent-600">Contacteer ons</a></li>
                        <li><a href="mailto:hallo@kotkompas.be" class="inline-block transition-colors hover:text-accent-600">hallo@kotkompas.be</a></li>
                    </ul>
                </div>
            </nav>
        </div>

        {{-- ── Baseline bar ── --}}
        <div class="flex flex-col gap-3 py-7 text-xs text-ink-soft sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} KotKompas. Alle rechten voorbehouden.</span>
            <span class="font-mono uppercase tracking-[0.16em]">Antwerpen · BE</span>
        </div>
    </div>
</footer>
