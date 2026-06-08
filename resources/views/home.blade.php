<x-layout title="KotKompas — Vind je kot, rechtstreeks van de eigenaar">

    <x-public-nav />

    {{-- Hero --}}
    <section class="mx-auto w-full max-w-6xl px-5 pt-14 pb-12 sm:px-6 sm:pt-20 sm:pb-16">
        <div class="max-w-2xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Studentenhuisvesting</p>
            <h1 class="text-4xl font-semibold leading-[1.05] tracking-tight text-primary-900 sm:text-5xl lg:text-6xl">
                Vind je kot, rechtstreeks van de eigenaar.
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-base-een-800">
                Zoek, vergelijk en plan bezichtigingen — zonder makelaarskosten of tussenpersoon. Alles op één plek.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#koten" class="inline-flex items-center justify-center rounded-md bg-primary-600 px-6 py-3 text-base font-semibold text-white transition hover:bg-primary-700">
                    Bekijk koten
                </a>
                <a href="{{ url('/dashboard/register') }}" class="inline-flex items-center justify-center rounded-md border border-base-twee-400 bg-white px-6 py-3 text-base font-semibold text-primary-900 transition hover:border-primary-300">
                    Verhuur je kot
                </a>
            </div>
        </div>
    </section>

    {{-- Featured koten --}}
    <section id="koten" class="scroll-mt-20 bg-base-een-200 py-14 sm:py-20">
        <div class="mx-auto w-full max-w-6xl px-5 sm:px-6">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-primary-900 sm:text-3xl">Uitgelichte koten</h2>
                    <p class="mt-2 text-base text-base-een-800">Een greep uit de beschikbare plekken.</p>
                </div>
            </div>

            @if ($featuredRooms->isNotEmpty())
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredRooms as $room)
                        <x-koten-card :room="$room" />
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-base-twee-400 bg-white px-6 py-14 text-center">
                    <p class="font-medium text-primary-900">Binnenkort beschikbaar</p>
                    <p class="mt-1 text-sm text-base-een-700">Er staan nog geen koten online. Kom snel terug of <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2">neem contact op</a>.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Hoe werkt het --}}
    <section class="mx-auto w-full max-w-6xl px-5 py-14 sm:px-6 sm:py-20">
        <h2 class="text-2xl font-semibold tracking-tight text-primary-900 sm:text-3xl">Hoe werkt het?</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-3">
            @php
                $steps = [
                    ['Direct', 'Huur rechtstreeks van de eigenaar, zonder makelaarskosten of tussenpersoon.'],
                    ['Overzicht', 'Zoek, vergelijk en plan bezichtigingen in jouw stad — alles op één plek.'],
                    ['Zeker', 'Geen verborgen kosten. Volledig transparant van zoeken tot huren.'],
                ];
            @endphp
            @foreach ($steps as $i => $step)
                <div class="rounded-xl border border-base-twee-300 bg-white p-6">
                    <span class="text-sm font-semibold text-accent-500">0{{ $i + 1 }}</span>
                    <h3 class="mt-2 text-lg font-semibold text-primary-900">{{ $step[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-base-een-800">{{ $step[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- FAQ teaser --}}
    @if ($faqCategories->isNotEmpty())
        <section class="bg-base-een-200 py-14 sm:py-20">
            <div class="mx-auto w-full max-w-2xl px-5 sm:px-6">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight text-primary-900 sm:text-3xl">Veelgestelde vragen</h2>
                    <a href="{{ route('faq') }}" class="shrink-0 text-sm font-medium text-primary-600 hover:text-primary-700">Alle vragen →</a>
                </div>

                <div class="overflow-hidden rounded-xl border border-base-twee-300 bg-white">
                    @foreach ($faqCategories as $category)
                        @foreach ($category->faqs as $faq)
                            <details class="group border-b border-base-twee-300 last:border-b-0">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left">
                                    <span class="font-medium text-primary-900">{{ $faq->vraag }}</span>
                                    <span class="text-accent-500 transition group-open:rotate-45">+</span>
                                </summary>
                                <div class="px-5 pb-4">
                                    <p class="text-sm leading-relaxed text-base-een-800">{{ $faq->antwoord }}</p>
                                </div>
                            </details>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA verhuurders --}}
    <section class="mx-auto w-full max-w-6xl px-5 py-14 sm:px-6 sm:py-20">
        <div class="rounded-2xl bg-primary-600 px-6 py-12 text-center sm:px-12">
            <h2 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Verhuur je kot zonder makelaarskosten</h2>
            <p class="mx-auto mt-3 max-w-xl text-primary-100">Plaats je pand en beheer aanvragen rechtstreeks in je dashboard.</p>
            <a href="{{ url('/dashboard/register') }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-white px-6 py-3 text-base font-semibold text-primary-700 transition hover:bg-base-een-200">
                Word verhuurder
            </a>
        </div>
    </section>

    <x-footer />
</x-layout>
