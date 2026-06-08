<x-layout title="Veelgestelde vragen · KotKompas">
<section class="mx-auto w-full max-w-2xl px-5 py-12 sm:px-6 sm:py-20">

    <header class="mb-10 sm:mb-12">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">FAQ</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Veelgestelde vragen
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Niet gevonden wat je zoekt?
            <a href="{{ route('contact') }}" class="font-medium text-primary-600 underline underline-offset-2 hover:text-primary-700">Neem contact op</a>.
        </p>
    </header>

    @forelse ($faqs as $faq)
        <details class="kk-faq group border-b border-base-twee-400 first:border-t">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left">
                <span class="text-lg font-medium text-primary-900">{{ $faq->vraag }}</span>
                <span class="kk-faq-mark relative h-5 w-5 shrink-0 text-accent-500" aria-hidden="true">
                    <span class="absolute left-1/2 top-1/2 h-0.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded bg-current"></span>
                    <span class="kk-faq-bar absolute left-1/2 top-1/2 h-3.5 w-0.5 -translate-x-1/2 -translate-y-1/2 rounded bg-current"></span>
                </span>
            </summary>
            <div class="pb-5 pr-9">
                <p class="max-w-prose whitespace-pre-line text-base leading-relaxed text-base-een-800">{{ $faq->antwoord }}</p>
            </div>
        </details>
    @empty
        <p class="rounded-md border border-base-twee-400 bg-base-een-100 px-4 py-6 text-center text-base-een-700">
            Er zijn nog geen vragen toegevoegd.
        </p>
    @endforelse
</section>

<style>
    /* hide the native disclosure triangle */
    .kk-faq summary::-webkit-details-marker { display: none; }
    /* + morphs to − when open: collapse the vertical bar */
    .kk-faq .kk-faq-bar { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1); }
    .kk-faq[open] .kk-faq-bar { transform: translate(-50%, -50%) scaleY(0); }
</style>
</x-layout>
