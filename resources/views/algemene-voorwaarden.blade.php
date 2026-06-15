<x-layout title="Algemene voorwaarden · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-3xl px-5 pb-24 pt-32 sm:px-8 sm:pt-36">

    {{-- Editorial header — display title carries the page, eyebrow + accent rule above --}}
    <header class="max-w-3xl" data-reveal>
        <p class="mb-6 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.2em] text-ink-soft">
            <span class="inline-block h-px w-10 bg-accent-500"></span> Juridisch
        </p>
        <h1 class="text-[clamp(2.6rem,7vw,5rem)] font-medium leading-[0.95] tracking-[-0.04em] text-ink">
            Algemene voorwaarden
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
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Over KotKompas</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas is een platform dat huurders en verhuurders van studentenkamers met elkaar in contact brengt.
                    KotKompas treedt uitsluitend op als tussenpersoon en is geen partij in de huurovereenkomst tussen
                    huurder en verhuurder. Door gebruik te maken van KotKompas ga je akkoord met deze algemene voorwaarden.
                </p>
            </div>
        </section>

        <section id="sec-2" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">02</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Gebruik van het platform</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Om gebruik te maken van KotKompas dien je:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Ten minste 18 jaar oud te zijn</li>
                    <li>Je te registreren met correcte en volledige gegevens</li>
                    <li>Je account persoonlijk te gebruiken en niet over te dragen aan derden</li>
                    <li>Je wachtwoord vertrouwelijk te houden</li>
                </ul>
            </div>
        </section>

        <section id="sec-3" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">03</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Accounts</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Je bent verantwoordelijk voor alle activiteiten die plaatsvinden via jouw account. Meld verdachte
                    activiteiten of ongeoorloofde toegang zo snel mogelijk via
                    <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">ons contactformulier</a>.
                    KotKompas behoudt het recht om accounts te deactiveren bij misbruik of schending van deze voorwaarden.
                </p>
            </div>
        </section>

        <section id="sec-4" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">04</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Verhuurders</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Als verhuurder op KotKompas verbind je je ertoe:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Enkel kamers aan te bieden waarover je beschikkingsbevoegdheid hebt</li>
                    <li>Correcte en volledige informatie te verstrekken over de aangeboden kamer</li>
                    <li>Alle toepasselijke wettelijke verplichtingen na te leven, waaronder conformiteitsnormen voor studentenhuisvesting</li>
                    <li>Geen misleidende, valse of bedrieglijke advertenties te plaatsen</li>
                </ul>
            </div>
        </section>

        <section id="sec-5" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">05</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Huurders</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Als huurder op KotKompas verbind je je ertoe:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Correct en eerlijk te communiceren met verhuurders</li>
                    <li>Afspraken die je maakt via het platform na te komen</li>
                    <li>Het platform niet te gebruiken voor commerciële doeleinden of doorverhuur</li>
                </ul>
            </div>
        </section>

        <section id="sec-6" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">06</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Verboden gebruik</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">Het is verboden om KotKompas te gebruiken voor:</p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>Het verspreiden van onwettige, beledigende of misleidende inhoud</li>
                    <li>Spam, phishing of andere vormen van misbruik</li>
                    <li>Het automatisch uitlezen van gegevens (scraping) zonder uitdrukkelijke toestemming</li>
                    <li>Het ondermijnen van de veiligheid of werking van het platform</li>
                    <li>Discriminatie op basis van afkomst, geslacht, religie of enig ander beschermd kenmerk</li>
                </ul>
            </div>
        </section>

        <section id="sec-7" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">07</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Aansprakelijkheid</h2>
                <p class="mb-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas is een tussenpersoon en is niet aansprakelijk voor:
                </p>
                <ul class="list-disc space-y-1.5 pl-5 text-[0.95rem] leading-relaxed text-ink-soft marker:text-accent-500">
                    <li>De inhoud van advertenties geplaatst door verhuurders</li>
                    <li>Geschillen tussen huurders en verhuurders</li>
                    <li>Schade die voortvloeit uit het gebruik van het platform</li>
                    <li>Tijdelijke onbeschikbaarheid van het platform door onderhoud of technische problemen</li>
                </ul>
                <p class="mt-3 text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas doet zijn best om het platform veilig en correct te houden, maar geeft geen garanties
                    over de volledigheid of juistheid van de aangeboden informatie.
                </p>
            </div>
        </section>

        <section id="sec-8" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">08</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Intellectuele eigendom</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Alle inhoud op KotKompas, waaronder teksten, logo's, afbeeldingen en code, is eigendom van KotKompas
                    of wordt gebruikt met toestemming van de rechthebbenden. Het is niet toegestaan deze inhoud te
                    kopiëren, te verspreiden of te gebruiken zonder uitdrukkelijke schriftelijke toestemming.
                </p>
            </div>
        </section>

        <section id="sec-9" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">09</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Privacy</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Het gebruik van je persoonsgegevens wordt beschreven in ons
                    <a href="{{ route('privacy') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">privacybeleid</a>.
                    Door gebruik te maken van KotKompas ga je ook akkoord met dat beleid.
                </p>
            </div>
        </section>

        <section id="sec-10" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">10</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Wijzigingen</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    KotKompas behoudt het recht om deze voorwaarden op elk moment te wijzigen. Bij ingrijpende wijzigingen
                    word je vooraf geïnformeerd via e-mail of een melding op het platform. Voortgezet gebruik van het
                    platform na de wijziging geldt als aanvaarding van de nieuwe voorwaarden.
                </p>
            </div>
        </section>

        <section id="sec-11" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-t border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">11</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Toepasselijk recht</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Op deze algemene voorwaarden is het Belgisch recht van toepassing. Geschillen worden voorgelegd
                    aan de bevoegde rechtbanken van België.
                </p>
            </div>
        </section>

        <section id="sec-12" class="grid scroll-mt-28 gap-x-8 gap-y-3 border-y border-hairline py-10 sm:grid-cols-[3.5rem_1fr] sm:py-12" data-reveal>
            <span class="kk-num text-2xl font-medium text-ink/25 sm:text-[1.75rem]" aria-hidden="true">12</span>
            <div class="max-w-[42rem]">
                <h2 class="mb-4 text-xl font-medium tracking-tight text-ink">Contact</h2>
                <p class="text-[0.95rem] leading-relaxed text-ink-soft">
                    Vragen over deze algemene voorwaarden? Neem contact op via
                    <a href="{{ route('contact') }}" class="font-medium text-ink underline decoration-1 underline-offset-2 transition-colors hover:text-secondary-600">ons contactformulier</a>.
                </p>
            </div>
        </section>

    </div>

</section>

<x-footer />
</x-layout>
