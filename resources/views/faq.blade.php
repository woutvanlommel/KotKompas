<x-layout title="Veelgestelde vragen · KotKompas" body-class="bg-canvas text-ink">
<x-public-nav :over-hero="true" />

{{-- Navy hero band — auth language --}}
<section class="relative -mt-[4.5rem] flex min-h-[48svh] items-end overflow-hidden bg-primary-900 pt-[4.5rem] text-white">
    <div class="absolute inset-0 z-0" aria-hidden="true">
        <img src="{{ asset('img/hero-bg.jpg') }}" alt="" class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-linear-to-r from-primary-900 via-primary-900/85 to-primary-900/40"></div>
        <div class="absolute inset-0 bg-linear-to-t from-primary-900 via-transparent to-primary-900/40"></div>
    </div>
    <div class="relative z-10 mx-auto w-full max-w-[88rem] px-5 pb-12 sm:px-8 sm:pb-16">
        <p class="mb-5 flex items-center gap-3 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-white/65">
            <span class="inline-block h-px w-8 bg-accent-500"></span> Hulp & info
        </p>
        <h1 data-split class="max-w-[14ch] text-[clamp(2.4rem,6vw,4.75rem)] font-medium leading-[0.92] tracking-[-0.04em] text-white">
            Veelgestelde vragen
        </h1>
    </div>
</section>

<section class="py-16 sm:py-24">
    <div class="mx-auto w-full max-w-3xl px-5 sm:px-8">
        <p class="mb-12 max-w-prose text-ink-soft" data-reveal>
            Niet gevonden wat je zoekt?
            <a href="{{ route('contact') }}" class="border-b border-ink/40 text-ink transition hover:border-accent-500">Neem contact op</a>.
        </p>

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
    </div>
</section>

<style>
    .kk-faq .kk-faq-bar { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }
    .kk-faq[open] .kk-faq-bar { transform: translate(-50%, -50%) scaleY(0); }
</style>

<x-footer />
</x-layout>
