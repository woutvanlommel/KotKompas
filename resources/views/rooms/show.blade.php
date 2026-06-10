{{-- Detailpagina voor een individuele kamer/kot.
     Momenteel een minimale weergave. Verder uitbreiden met foto's, faciliteiten, contactformulier, etc. --}}

<x-layout :title="($room->title ?? 'Kot') . ' — KotKompas'" body-class="bg-canvas text-ink">

    <x-public-nav />

    <section class="mx-auto w-full max-w-[88rem] px-5 pb-24 pt-32 sm:px-8">

        <a href="{{ route('rooms.index') }}"
           class="mb-8 inline-flex items-center gap-2 text-sm text-ink/55 hover:text-ink">
            ← Terug naar overzicht
        </a>

        <h1 class="text-[clamp(2rem,4vw,3.5rem)] font-medium leading-[0.9] tracking-[-0.04em]">
            {{ $room->title ?? 'Kot' }}
        </h1>

        <p class="mt-3 text-ink/60">{{ $room->building?->full_address }}</p>

        <p class="mt-8 text-2xl font-medium">
            €{{ number_format((float) $room->price_per_month, 0, ',', '.') }}<span class="ml-1 text-sm font-normal text-ink/55">/maand</span>
        </p>

        @if ($room->description)
            <p class="mt-6 max-w-2xl text-ink/75 leading-relaxed">{{ $room->description }}</p>
        @endif

    </section>

    <x-footer />

</x-layout>
