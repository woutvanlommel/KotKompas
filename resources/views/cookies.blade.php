<x-layout title="Cookiebeleid · KotKompas"
    description="Lees welke cookies KotKompas gebruikt, waarvoor ze dienen en hoe je je voorkeuren beheert."
    body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-3xl px-5 pb-24 pt-32 sm:px-8 sm:pt-36">

    {{-- Editorial header — display title carries the page, eyebrow + accent rule above --}}
    <header class="max-w-3xl" data-reveal>
        <p class="mb-6 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.2em] text-ink-soft">
            <span class="inline-block h-px w-10 bg-accent-500"></span> Juridisch
        </p>
        <h1 class="text-[clamp(2.6rem,7vw,5rem)] font-medium leading-[0.95] tracking-[-0.04em] text-ink">
            Cookiebeleid
        </h1>
        <p class="mt-6 text-sm text-ink-soft">
            Laatst bijgewerkt: {{ now()->translatedFormat('j F Y') }}
        </p>
    </header>

    {{-- Numbered sections: mono numeral in the gutter, hairline-divided, fade-up on scroll --}}
    <div class="mt-16 sm:mt-20">

        <section id="sec-1" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">01</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Wat zijn cookies?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Cookies zijn kleine tekstbestanden die op je apparaat worden opgeslagen wanneer je een website bezoekt.
                    Ze helpen de website om je voorkeuren te onthouden en zorgen ervoor dat bepaalde functies correct werken.
                </p>
            </div>
        </section>

        <section id="sec-2" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">02</span>
            <div class="max-w-none">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Welke cookies gebruikt KotKompas?</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">We maken uitsluitend gebruik van functionele cookies. We plaatsen geen tracking-, marketing- of advertentiecookies.</p>

                <div class="overflow-x-auto rounded-lg border border-hairline">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-hairline bg-ink/[0.03]">
                                <th class="px-4 py-3 text-left font-semibold text-ink">Cookie</th>
                                <th class="px-4 py-3 text-left font-semibold text-ink">Doel</th>
                                <th class="px-4 py-3 text-left font-semibold text-ink">Bewaartermijn</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ config('session.cookie', 'laravel_session') }}</td>
                                <td class="px-4 py-3">Houdt je inlogsessie actief</td>
                                <td class="px-4 py-3">{{ config('session.lifetime', 120) }} minuten</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">XSRF-TOKEN</td>
                                <td class="px-4 py-3">Beschermt formulieren tegen vervalsing (CSRF)</td>
                                <td class="px-4 py-3">Sessie</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="sec-3" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">03</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Zijn deze cookies verplicht?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Ja. De cookies die wij plaatsen zijn strikt noodzakelijk voor de werking van het platform.
                    Zonder deze cookies kun je niet inloggen en werken formulieren niet correct.
                    Omdat het gaat om functioneel noodzakelijke cookies, is hiervoor geen toestemming vereist.
                </p>
            </div>
        </section>

        <section id="sec-4" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">04</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Cookies van derden</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas biedt de mogelijkheid om in te loggen via Google (Google OAuth). Wanneer je hiervan gebruik maakt,
                    kan Google cookies plaatsen op basis van hun eigen cookiebeleid. We hebben hier geen controle over.
                    Meer informatie vind je in het
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">privacybeleid van Google</a>.
                </p>
            </div>
        </section>

        <section id="sec-5" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">05</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Cookies beheren of verwijderen</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Je kunt cookies beheren of verwijderen via de instellingen van je browser. Houd er rekening mee dat
                    het uitschakelen van cookies de werking van KotKompas kan beïnvloeden.
                </p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/nl/kb/cookies-verwijderen-gegevens-wissen-websites-opgeslagen" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/nl-be/guide/safari/sfri11471/mac" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">Safari</a></li>
                    <li><a href="https://support.microsoft.com/nl-nl/windows/cookies-verwijderen-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">Microsoft Edge</a></li>
                </ul>
            </div>
        </section>

        <section id="sec-6" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-y border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">06</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Vragen?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Heb je vragen over ons cookiebeleid? Neem dan contact op via
                    <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">ons contactformulier</a>.
                </p>
            </div>
        </section>

    </div>

</section>

<x-footer />
</x-layout>
