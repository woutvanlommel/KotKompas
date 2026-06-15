<x-layout title="Je gegevens verwijderen · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-3xl px-5 pb-24 pt-32 sm:px-8 sm:pt-36">

    {{-- Editorial header — display title carries the page, eyebrow + accent rule above --}}
    <header class="max-w-3xl" data-reveal>
        <p class="mb-6 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.2em] text-ink-soft">
            <span class="inline-block h-px w-10 bg-accent-500"></span> Juridisch
        </p>
        <h1 class="text-[clamp(2.6rem,7vw,5rem)] font-medium leading-[0.95] tracking-[-0.04em] text-ink">
            Je gegevens verwijderen
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
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Je account en gegevens laten verwijderen</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Wil je dat we je account en alle bijbehorende persoonsgegevens verwijderen? Stuur ons een verzoek en we handelen het binnen 30 dagen af:
                </p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Via het <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">contactformulier</a>, met als onderwerp "Gegevens verwijderen"</li>
                    <li>Of per e-mail naar <a href="mailto:hallo@kotkompas.be" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">hallo@kotkompas.be</a> vanaf het e-mailadres van je account</li>
                </ul>
                <p class="mt-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Na verwijdering zijn je profielgegevens, foto's en berichten niet langer toegankelijk. Gegevens die we wettelijk verplicht moeten bewaren (bijvoorbeeld voor boekhouding) bewaren we niet langer dan de wettelijke termijn.
                </p>
            </div>
        </section>

        <section id="sec-2" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">02</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Ingelogd via Facebook of Google?</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Heb je je aangemeld met je Facebook- of Google-account, dan kun je de koppeling met KotKompas op elk moment intrekken:
                </p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li><span class="font-medium">Facebook:</span> ga naar Instellingen &rarr; Apps en websites, en verwijder KotKompas uit de lijst.</li>
                    <li><span class="font-medium">Google:</span> ga naar je Google-account &rarr; Beveiliging &rarr; Verbindingen met apps en services van derden, en verwijder KotKompas.</li>
                </ul>
                <p class="mt-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Let op: het intrekken van die koppeling verwijdert je KotKompas-account niet. Wil je ook je account en gegevens bij ons kwijt, dien dan een verwijderverzoek in zoals beschreven onder punt 1.
                </p>
            </div>
        </section>

        <section id="sec-3" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-y border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">03</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Welke gegevens bewaren we?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Een overzicht van welke persoonsgegevens we verwerken en waarom vind je in ons
                    <a href="{{ route('privacy') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">privacybeleid</a>.
                </p>
            </div>
        </section>

    </div>

</section>

<x-footer />
</x-layout>
