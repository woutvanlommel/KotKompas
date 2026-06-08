<x-layout title="Veelgestelde vragen · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav />

<section class="mx-auto w-full max-w-3xl px-5 pt-28 pb-16 sm:px-8 sm:pt-32 sm:pb-24">
    <header class="mb-12" data-reveal>
        <p class="mb-4 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-ink-soft">
            <span class="inline-block h-px w-8 bg-accent-500"></span> Hulp & info
        </p>
        <h1 data-split class="text-[clamp(2.4rem,6vw,4.5rem)] font-medium leading-[0.92] tracking-[-0.04em] text-ink">
            Veelgestelde vragen
        </h1>
        <p class="mt-5 max-w-prose text-ink-soft">
            Niet gevonden wat je zoekt?
            <a href="{{ route('contact') }}" class="border-b border-ink/40 text-ink transition hover:border-accent-500">Neem contact op</a>.
        </p>
    </header>

    @forelse ($categories as $category)
        <div class="mb-14 last:mb-0" data-reveal>
            <h2 class="mb-1 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ink-soft">{{ $category->naam }}</h2>
            @foreach ($category->faqs as $faq)
                <details class="kk-faq group border-t border-hairline last:border-b">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left [&::-webkit-details-marker]:hidden">
                        <span class="font-medium tracking-tight text-ink">{{ $faq->vraag }}</span>
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
</section>

<style>
    .kk-faq .kk-faq-bar { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }
    .kk-faq[open] .kk-faq-bar { transform: translate(-50%, -50%) scaleY(0); }
</style>

<x-footer />
</x-layout>
