<x-layout title="Privacybeleid · KotKompas">
<section class="mx-auto w-full max-w-2xl px-5 py-12 sm:px-6 sm:py-20">

    <header class="mb-10 sm:mb-12">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Juridisch</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Privacybeleid
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Laatst bijgewerkt: {{ now()->translatedFormat('j F Y') }}
        </p>
    </header>

    <div class="space-y-10 text-base leading-relaxed text-base-een-800">

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">1. Wie zijn wij?</h2>
            <p>
                KotKompas is een platform dat huurders en verhuurders van studentenkamers met elkaar in contact brengt.
                In dit privacybeleid leggen we uit welke persoonsgegevens we verzamelen, waarom we dat doen en hoe we daarmee omgaan.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">2. Welke gegevens verzamelen we?</h2>
            <p class="mb-3">Bij het gebruik van KotKompas kunnen de volgende persoonsgegevens worden verwerkt:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Naam en e-mailadres</li>
                <li>Telefoonnummer (optioneel)</li>
                <li>Geboortedatum</li>
                <li>Profielfoto</li>
                <li>Gegevens gekoppeld aan een Google-account bij inloggen via Google</li>
                <li>Berichten verstuurd via het contactformulier</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">3. Waarvoor gebruiken we je gegevens?</h2>
            <p class="mb-3">We verwerken je gegevens uitsluitend voor de volgende doeleinden:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Het aanmaken en beheren van je account</li>
                <li>Het faciliteren van contact tussen huurders en verhuurders</li>
                <li>Het beantwoorden van vragen via het contactformulier</li>
                <li>Het verbeteren van onze diensten</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">4. Delen we je gegevens?</h2>
            <p>
                We verkopen je gegevens nooit aan derden. Gegevens worden alleen gedeeld voor zover dat noodzakelijk is
                voor de werking van het platform, bijvoorbeeld met hostingproviders die onze infrastructuur beheren.
                Deze partijen zijn gebonden aan verwerkersovereenkomsten.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">5. Hoe lang bewaren we je gegevens?</h2>
            <p>
                Je gegevens worden bewaard zolang je account actief is. Wanneer je je account verwijdert, worden je
                persoonsgegevens binnen 30 dagen uit onze systemen verwijderd, tenzij wettelijke verplichtingen een
                langere bewaartermijn vereisen.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">6. Cookies</h2>
            <p>
                KotKompas maakt gebruik van functionele cookies die noodzakelijk zijn voor de werking van het platform,
                zoals sessiecookies voor het inloggen. We plaatsen geen tracking- of advertentiecookies.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">7. Jouw rechten</h2>
            <p class="mb-3">Als gebruiker heb je de volgende rechten met betrekking tot je persoonsgegevens:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Recht op inzage van je gegevens</li>
                <li>Recht op correctie van onjuiste gegevens</li>
                <li>Recht op verwijdering van je gegevens</li>
                <li>Recht op beperking van de verwerking</li>
                <li>Recht op overdraagbaarheid van gegevens</li>
            </ul>
            <p class="mt-3">
                Om een van deze rechten uit te oefenen, kun je contact opnemen via
                <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">ons contactformulier</a>.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">8. Beveiliging</h2>
            <p>
                We nemen passende technische en organisatorische maatregelen om je persoonsgegevens te beschermen
                tegen ongeoorloofde toegang, verlies of misbruik. Wachtwoorden worden versleuteld opgeslagen en
                verbindingen zijn beveiligd via HTTPS.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">9. Klachten</h2>
            <p>
                Heb je een klacht over hoe we met je gegevens omgaan? Neem dan eerst contact met ons op via
                <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">het contactformulier</a>.
                Je hebt ook het recht om een klacht in te dienen bij de Gegevensbeschermingsautoriteit (GBA)
                via <a href="https://www.gegevensbeschermingsautoriteit.be" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">gegevensbeschermingsautoriteit.be</a>.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">10. Wijzigingen</h2>
            <p>
                We kunnen dit privacybeleid van tijd tot tijd aanpassen. Bij ingrijpende wijzigingen stellen we je
                hiervan op de hoogte via e-mail of een melding op het platform. De meest recente versie is altijd
                beschikbaar op deze pagina.
            </p>
        </div>

    </div>

</section>
</x-layout>
