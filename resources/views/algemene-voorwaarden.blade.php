<x-layout title="Algemene voorwaarden · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-2xl px-5 pt-32 pb-16 sm:px-6 sm:pt-36 sm:pb-24">

    <header class="mb-10 sm:mb-12">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Juridisch</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Algemene voorwaarden
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Laatst bijgewerkt: {{ now()->translatedFormat('j F Y') }}
        </p>
    </header>

    <div class="space-y-10 text-base leading-relaxed text-base-een-800">

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">1. Over KotKompas</h2>
            <p>
                KotKompas is een platform dat huurders en verhuurders van studentenkamers met elkaar in contact brengt.
                KotKompas treedt uitsluitend op als tussenpersoon en is geen partij in de huurovereenkomst tussen
                huurder en verhuurder. Door gebruik te maken van KotKompas ga je akkoord met deze algemene voorwaarden.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">2. Gebruik van het platform</h2>
            <p class="mb-3">Om gebruik te maken van KotKompas dien je:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Ten minste 18 jaar oud te zijn</li>
                <li>Je te registreren met correcte en volledige gegevens</li>
                <li>Je account persoonlijk te gebruiken en niet over te dragen aan derden</li>
                <li>Je wachtwoord vertrouwelijk te houden</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">3. Accounts</h2>
            <p>
                Je bent verantwoordelijk voor alle activiteiten die plaatsvinden via jouw account. Meld verdachte
                activiteiten of ongeoorloofde toegang zo snel mogelijk via
                <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">ons contactformulier</a>.
                KotKompas behoudt het recht om accounts te deactiveren bij misbruik of schending van deze voorwaarden.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">4. Verhuurders</h2>
            <p class="mb-3">Als verhuurder op KotKompas verbind je je ertoe:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Enkel kamers aan te bieden waarover je beschikkingsbevoegdheid hebt</li>
                <li>Correcte en volledige informatie te verstrekken over de aangeboden kamer</li>
                <li>Alle toepasselijke wettelijke verplichtingen na te leven, waaronder conformiteitsnormen voor studentenhuisvesting</li>
                <li>Geen misleidende, valse of bedrieglijke advertenties te plaatsen</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">5. Huurders</h2>
            <p class="mb-3">Als huurder op KotKompas verbind je je ertoe:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Correct en eerlijk te communiceren met verhuurders</li>
                <li>Afspraken die je maakt via het platform na te komen</li>
                <li>Het platform niet te gebruiken voor commerciële doeleinden of doorverhuur</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">6. Verboden gebruik</h2>
            <p class="mb-3">Het is verboden om KotKompas te gebruiken voor:</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Het verspreiden van onwettige, beledigende of misleidende inhoud</li>
                <li>Spam, phishing of andere vormen van misbruik</li>
                <li>Het automatisch uitlezen van gegevens (scraping) zonder uitdrukkelijke toestemming</li>
                <li>Het ondermijnen van de veiligheid of werking van het platform</li>
                <li>Discriminatie op basis van afkomst, geslacht, religie of enig ander beschermd kenmerk</li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">7. Aansprakelijkheid</h2>
            <p class="mb-3">
                KotKompas is een tussenpersoon en is niet aansprakelijk voor:
            </p>
            <ul class="list-disc space-y-1 pl-5">
                <li>De inhoud van advertenties geplaatst door verhuurders</li>
                <li>Geschillen tussen huurders en verhuurders</li>
                <li>Schade die voortvloeit uit het gebruik van het platform</li>
                <li>Tijdelijke onbeschikbaarheid van het platform door onderhoud of technische problemen</li>
            </ul>
            <p class="mt-3">
                KotKompas doet zijn best om het platform veilig en correct te houden, maar geeft geen garanties
                over de volledigheid of juistheid van de aangeboden informatie.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">8. Intellectuele eigendom</h2>
            <p>
                Alle inhoud op KotKompas, waaronder teksten, logo's, afbeeldingen en code, is eigendom van KotKompas
                of wordt gebruikt met toestemming van de rechthebbenden. Het is niet toegestaan deze inhoud te
                kopiëren, te verspreiden of te gebruiken zonder uitdrukkelijke schriftelijke toestemming.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">9. Privacy</h2>
            <p>
                Het gebruik van je persoonsgegevens wordt beschreven in ons
                <a href="{{ route('privacy') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">privacybeleid</a>.
                Door gebruik te maken van KotKompas ga je ook akkoord met dat beleid.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">10. Wijzigingen</h2>
            <p>
                KotKompas behoudt het recht om deze voorwaarden op elk moment te wijzigen. Bij ingrijpende wijzigingen
                word je vooraf geïnformeerd via e-mail of een melding op het platform. Voortgezet gebruik van het
                platform na de wijziging geldt als aanvaarding van de nieuwe voorwaarden.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">11. Toepasselijk recht</h2>
            <p>
                Op deze algemene voorwaarden is het Belgisch recht van toepassing. Geschillen worden voorgelegd
                aan de bevoegde rechtbanken van België.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">12. Contact</h2>
            <p>
                Vragen over deze algemene voorwaarden? Neem contact op via
                <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">ons contactformulier</a>.
            </p>
        </div>

    </div>

</section>

<x-footer />
</x-layout>
