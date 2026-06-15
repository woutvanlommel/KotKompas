<x-layout title="Gegevens verwijderen · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-2xl px-5 pt-32 pb-16 sm:px-6 sm:pt-36 sm:pb-24">

    <header class="mb-10 sm:mb-12">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Juridisch</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Je gegevens verwijderen
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Laatst bijgewerkt: {{ now()->translatedFormat('j F Y') }}
        </p>
    </header>

    <div class="space-y-10 text-base leading-relaxed text-base-een-800">

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">1. Je account en gegevens laten verwijderen</h2>
            <p class="mb-3">
                Wil je dat we je account en alle bijbehorende persoonsgegevens verwijderen? Stuur ons een verzoek en we handelen het binnen 30 dagen af:
            </p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Via het <a href="{{ route('contact') }}" class="font-medium text-primary-900 underline underline-offset-2 transition-colors hover:text-secondary-600">contactformulier</a>, met als onderwerp "Gegevens verwijderen"</li>
                <li>Of per e-mail naar <a href="mailto:hallo@kotkompas.be" class="font-medium text-primary-900 underline underline-offset-2 transition-colors hover:text-secondary-600">hallo@kotkompas.be</a> vanaf het e-mailadres van je account</li>
            </ul>
            <p class="mt-3">
                Na verwijdering zijn je profielgegevens, foto's en berichten niet langer toegankelijk. Gegevens die we wettelijk verplicht moeten bewaren (bijvoorbeeld voor boekhouding) bewaren we niet langer dan de wettelijke termijn.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">2. Ingelogd via Facebook of Google?</h2>
            <p class="mb-3">
                Heb je je aangemeld met je Facebook- of Google-account, dan kun je de koppeling met KotKompas op elk moment intrekken:
            </p>
            <ul class="list-disc space-y-1 pl-5">
                <li><span class="font-medium">Facebook:</span> ga naar Instellingen &rarr; Apps en websites, en verwijder KotKompas uit de lijst.</li>
                <li><span class="font-medium">Google:</span> ga naar je Google-account &rarr; Beveiliging &rarr; Verbindingen met apps en services van derden, en verwijder KotKompas.</li>
            </ul>
            <p class="mt-3">
                Let op: het intrekken van die koppeling verwijdert je KotKompas-account niet. Wil je ook je account en gegevens bij ons kwijt, dien dan een verwijderverzoek in zoals beschreven onder punt 1.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">3. Welke gegevens bewaren we?</h2>
            <p>
                Een overzicht van welke persoonsgegevens we verwerken en waarom vind je in ons
                <a href="{{ route('privacy') }}" class="font-medium text-primary-900 underline underline-offset-2 transition-colors hover:text-secondary-600">privacybeleid</a>.
            </p>
        </div>

    </div>

</section>

<x-footer />
</x-layout>
