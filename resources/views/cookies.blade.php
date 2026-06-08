<x-layout title="Cookiebeleid · KotKompas">
<section class="mx-auto w-full max-w-2xl px-5 py-12 sm:px-6 sm:py-20">

    <header class="mb-10 sm:mb-12">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Juridisch</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Cookiebeleid
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Laatst bijgewerkt: {{ now()->translatedFormat('j F Y') }}
        </p>
    </header>

    <div class="space-y-10 text-base leading-relaxed text-base-een-800">

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">1. Wat zijn cookies?</h2>
            <p>
                Cookies zijn kleine tekstbestanden die op je apparaat worden opgeslagen wanneer je een website bezoekt.
                Ze helpen de website om je voorkeuren te onthouden en zorgen ervoor dat bepaalde functies correct werken.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">2. Welke cookies gebruikt KotKompas?</h2>
            <p class="mb-4">We maken uitsluitend gebruik van functionele cookies. We plaatsen geen tracking-, marketing- of advertentiecookies.</p>

            <div class="overflow-x-auto rounded-lg border border-base-twee-400">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-base-twee-400 bg-base-een-100">
                            <th class="px-4 py-3 text-left font-semibold text-primary-900">Cookie</th>
                            <th class="px-4 py-3 text-left font-semibold text-primary-900">Doel</th>
                            <th class="px-4 py-3 text-left font-semibold text-primary-900">Bewaartermijn</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-twee-400">
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

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">3. Zijn deze cookies verplicht?</h2>
            <p>
                Ja. De cookies die wij plaatsen zijn strikt noodzakelijk voor de werking van het platform.
                Zonder deze cookies kun je niet inloggen en werken formulieren niet correct.
                Omdat het gaat om functioneel noodzakelijke cookies, is hiervoor geen toestemming vereist.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">4. Cookies van derden</h2>
            <p>
                KotKompas biedt de mogelijkheid om in te loggen via Google (Google OAuth). Wanneer je hiervan gebruik maakt,
                kan Google cookies plaatsen op basis van hun eigen cookiebeleid. We hebben hier geen controle over.
                Meer informatie vind je in het
                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">privacybeleid van Google</a>.
            </p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">5. Cookies beheren of verwijderen</h2>
            <p class="mb-3">
                Je kunt cookies beheren of verwijderen via de instellingen van je browser. Houd er rekening mee dat
                het uitschakelen van cookies de werking van KotKompas kan beïnvloeden.
            </p>
            <ul class="list-disc space-y-1 pl-5">
                <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">Google Chrome</a></li>
                <li><a href="https://support.mozilla.org/nl/kb/cookies-verwijderen-gegevens-wissen-websites-opgeslagen" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">Mozilla Firefox</a></li>
                <li><a href="https://support.apple.com/nl-be/guide/safari/sfri11471/mac" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">Safari</a></li>
                <li><a href="https://support.microsoft.com/nl-nl/windows/cookies-verwijderen-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">Microsoft Edge</a></li>
            </ul>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-primary-900">6. Vragen?</h2>
            <p>
                Heb je vragen over ons cookiebeleid? Neem dan contact op via
                <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">ons contactformulier</a>.
            </p>
        </div>

    </div>

</section>
</x-layout>
