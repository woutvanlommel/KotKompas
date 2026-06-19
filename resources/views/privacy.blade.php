<x-layout title="Privacybeleid · KotKompas"
    description="Lees hoe KotKompas omgaat met je persoonsgegevens, welke data we verwerken en wat je privacyrechten zijn."
    body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-3xl px-5 pb-24 pt-32 sm:px-8 sm:pt-36">

    {{-- Editorial header — display title carries the page, eyebrow + accent rule above --}}
    <header class="max-w-3xl" data-reveal>
        <p class="mb-6 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.2em] text-ink-soft">
            <span class="inline-block h-px w-10 bg-accent-500"></span> Juridisch
        </p>
        <h1 class="text-[clamp(2.6rem,7vw,5rem)] font-medium leading-[0.95] tracking-[-0.04em] text-ink">
            Privacybeleid
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
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Wie zijn wij?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas is een platform dat huurders en verhuurders van studentenkamers met elkaar in contact brengt.
                    In dit privacybeleid leggen we uit welke persoonsgegevens we verzamelen, waarom we dat doen en hoe we daarmee omgaan.
                </p>
            </div>
        </section>

        <section id="sec-2" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">02</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Welke gegevens verzamelen we?</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Bij het gebruik van KotKompas kunnen de volgende persoonsgegevens worden verwerkt:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Naam en e-mailadres</li>
                    <li>Telefoonnummer (optioneel)</li>
                    <li>Geboortedatum</li>
                    <li>Profielfoto</li>
                    <li>Gegevens gekoppeld aan een Google- of Facebook-account bij inloggen via Google of Facebook</li>
                    <li>Berichten verstuurd via het contactformulier</li>
                </ul>
            </div>
        </section>

        <section id="sec-3" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">03</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Waarvoor gebruiken we je gegevens?</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">We verwerken je gegevens uitsluitend voor de volgende doeleinden:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Het aanmaken en beheren van je account</li>
                    <li>Het faciliteren van contact tussen huurders en verhuurders</li>
                    <li>Het beantwoorden van vragen via het contactformulier</li>
                    <li>Het verbeteren van onze diensten</li>
                </ul>
            </div>
        </section>

        <section id="sec-4" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">04</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Delen we je gegevens?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    We verkopen je gegevens nooit aan derden. Gegevens worden alleen gedeeld voor zover dat noodzakelijk is
                    voor de werking van het platform, bijvoorbeeld met hostingproviders die onze infrastructuur beheren.
                    Deze partijen zijn gebonden aan verwerkersovereenkomsten.
                </p>
            </div>
        </section>

        <section id="sec-5" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">05</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Hoe lang bewaren we je gegevens?</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Je gegevens worden bewaard zolang je account actief is. Wanneer je je account verwijdert, worden je
                    persoonsgegevens binnen 30 dagen uit onze systemen verwijderd, tenzij wettelijke verplichtingen een
                    langere bewaartermijn vereisen.
                </p>
            </div>
        </section>

        <section id="sec-6" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">06</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Cookies</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas maakt gebruik van functionele cookies die noodzakelijk zijn voor de werking van het platform,
                    zoals sessiecookies voor het inloggen. We plaatsen geen tracking- of advertentiecookies.
                </p>
            </div>
        </section>

        <section id="sec-7" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">07</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Jouw rechten</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Als gebruiker heb je de volgende rechten met betrekking tot je persoonsgegevens:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Recht op inzage van je gegevens</li>
                    <li>Recht op correctie van onjuiste gegevens</li>
                    <li>Recht op verwijdering van je gegevens</li>
                    <li>Recht op beperking van de verwerking</li>
                    <li>Recht op overdraagbaarheid van gegevens</li>
                </ul>
                <p class="mt-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    Om een van deze rechten uit te oefenen, kun je contact opnemen via
                    <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">ons contactformulier</a>.
                </p>
            </div>
        </section>

        <section id="sec-8" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">08</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Beveiliging</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    We nemen passende technische en organisatorische maatregelen om je persoonsgegevens te beschermen
                    tegen ongeoorloofde toegang, verlies of misbruik. Wachtwoorden worden versleuteld opgeslagen en
                    verbindingen zijn beveiligd via HTTPS.
                </p>
            </div>
        </section>

        <section id="sec-9" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">09</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Klachten</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Heb je een klacht over hoe we met je gegevens omgaan? Neem dan eerst contact met ons op via
                    <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">het contactformulier</a>.
                    Je hebt ook het recht om een klacht in te dienen bij de Gegevensbeschermingsautoriteit (GBA)
                    via <a href="https://www.gegevensbeschermingsautoriteit.be" target="_blank" rel="noopener" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">gegevensbeschermingsautoriteit.be</a>.
                </p>
            </div>
        </section>

        <section id="sec-10" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-y border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">10</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Wijzigingen</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    We kunnen dit privacybeleid van tijd tot tijd aanpassen. Bij ingrijpende wijzigingen stellen we je
                    hiervan op de hoogte via e-mail of een melding op het platform. De meest recente versie is altijd
                    beschikbaar op deze pagina.
                </p>
            </div>
        </section>

    </div>

</section>

<x-footer />
</x-layout>
