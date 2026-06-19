<x-layout title="Veelgestelde vragen · KotKompas"
    description="Antwoorden op veelgestelde vragen over koten zoeken, bezichtigingen, de KotScore en verhuren via KotKompas."
    body-class="bg-canvas text-ink">

    <x-slot:head>
        @php
            $faqItems = $categories
                ->flatMap(fn ($category) => $category->faqs)
                ->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name'  => $faq->vraag,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $faq->antwoord,
                    ],
                ])
                ->values()
                ->all();
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type'    => 'FAQPage',
                'mainEntity' => $faqItems,
            ];
        @endphp
        @if (! empty($faqItems))
            <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
    </x-slot:head>

<x-public-nav />

<section class="mx-auto w-full max-w-[88rem] px-5 pt-32 pb-24 sm:px-8 sm:pt-36">
    <div class="grid gap-x-16 gap-y-12 lg:grid-cols-[0.78fr_1.22fr]">

        {{-- ── Left rail: heading · category index · live search · contact ── --}}
        <div class="lg:sticky lg:top-28 lg:self-start">
            <p class="mb-5 flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.16em] text-ink-soft" data-reveal>
                <span class="inline-block h-px w-9 bg-accent-500"></span> Hulp &amp; info
            </p>
            <h1 data-split class="text-[clamp(2.4rem,5.5vw,4.5rem)] font-medium leading-[0.88] tracking-[-0.05em] text-balance text-ink">
                Veelgestelde <span class="kk-serif-it font-normal text-secondary-600">vragen</span>
            </h1>

            @if ($categories->isNotEmpty())
                {{-- live search --}}
                <div class="mt-9" data-reveal>
                    <label class="block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Zoek in vragen</label>
                    <div class="mt-2 flex items-center gap-2 border-b border-hairline transition-colors focus-within:border-ink">
                        <svg class="h-4 w-4 shrink-0 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        <input data-faq-search type="text" placeholder="Typ een trefwoord…" aria-label="Zoek in veelgestelde vragen"
                               class="w-full bg-transparent py-2.5 text-lg text-ink placeholder:text-ink-soft focus:outline-none">
                    </div>
                    <p data-faq-empty hidden class="mt-3 text-sm text-ink-soft">Geen vragen gevonden. <a href="{{ route('contact') }}" class="text-ink underline-offset-4 transition hover:text-secondary-600 hover:underline">Stel je vraag →</a></p>
                </div>

                {{-- category jump-nav --}}
                <nav class="mt-8 border-t border-hairline pt-6" data-reveal aria-label="Categorieën">
                    <p class="mb-3 text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Onderwerpen</p>
                    @foreach ($categories as $i => $category)
                        <a href="#cat-{{ $category->id }}" class="group flex items-baseline gap-3 py-1.5 text-sm tracking-tight text-ink-soft transition-colors hover:text-secondary-600">
                            <span class="kk-num text-xs text-accent-500">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $category->naam }}
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="mt-8 border-t border-hairline pt-6" data-reveal>
                <p class="text-sm leading-relaxed text-ink-soft">Geen antwoord op je vraag gevonden?</p>
                <a href="{{ route('contact') }}" class="group mt-3 inline-flex items-center gap-2 text-sm font-medium text-ink transition hover:text-secondary-600">
                    Contacteer ons <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>

        {{-- ── Right: categorised Q&A ── --}}
        <div data-faq-list>
            @forelse ($categories as $i => $category)
                <div id="cat-{{ $category->id }}" data-faq-cat class="mb-16 scroll-mt-28 last:mb-0" data-reveal>
                    <div class="mb-2 flex items-baseline gap-4 border-b border-ink/15 pb-3">
                        <span class="kk-num text-3xl text-ink/25 sm:text-4xl">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h2 class="text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ink-soft">{{ $category->naam }}</h2>
                    </div>
                    @foreach ($category->faqs as $faq)
                        <details class="kk-faq group border-t border-hairline last:border-b" data-faq-item data-q="{{ Str::lower($faq->vraag . ' ' . $faq->antwoord) }}">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left [&::-webkit-details-marker]:hidden">
                                <span class="font-medium tracking-tight text-ink transition-colors group-hover:text-secondary-600">{{ $faq->vraag }}</span>
                                <span class="kk-faq-mark relative h-5 w-5 shrink-0 text-accent-500" aria-hidden="true">
                                    <span class="absolute left-1/2 top-1/2 h-0.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded bg-current"></span>
                                    <span class="kk-faq-bar absolute left-1/2 top-1/2 h-3.5 w-0.5 -translate-x-1/2 -translate-y-1/2 rounded bg-current"></span>
                                </span>
                            </summary>
                            <div class="pb-5 pr-9">
                                <p class="max-w-2xl whitespace-pre-line leading-relaxed text-ink-soft">{{ $faq->antwoord }}</p>
                            </div>
                        </details>
                    @endforeach
                </div>
            @empty
                <p class="border-y border-hairline py-16 text-center text-ink-soft">Er zijn nog geen vragen toegevoegd.</p>
            @endforelse
        </div>
    </div>
</section>

<style>
    .kk-faq .kk-faq-bar { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }
    .kk-faq[open] .kk-faq-bar { transform: translate(-50%, -50%) scaleY(0); }
</style>

<x-footer />
</x-layout>
