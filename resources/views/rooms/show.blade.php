{{-- Detail page for an individual room.
     Components live in resources/views/components/room/.
     Alpine skeleton: <x-room.skeleton> visible until Alpine boots → fade to content. --}}

<x-layout :title="($room->title ?? 'Kot') . ' — KotKompas'" body-class="bg-canvas text-ink">

    <x-public-nav />

    <section class="mx-auto w-full max-w-[88rem] px-5 pb-24 pt-32 sm:px-8">

        <a href="{{ route('rooms.index') }}"
           class="mb-10 inline-flex items-center gap-2 text-sm text-ink/55 transition-colors hover:text-ink">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Terug naar overzicht
        </a>

        {{-- Alpine wrapper: skeleton tot x-init vuurt --}}
        <div x-data="{ ready: false }" x-init="$nextTick(() => ready = true)">

            {{-- Skeleton: zichtbaar zolang ready = false --}}
            <div x-show="!ready">
                <x-room.skeleton />
            </div>

            {{-- Real content: style="display:none" so Alpine manages this itself
                 (x-cloak requires the CSS rule; an inline style is more reliable) --}}
            <div style="display:none" x-show="ready"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">

                <x-room.header :room="$room" />

                <x-room.gallery :room="$room" />

                <div class="mt-10 space-y-12 md:mt-16 md:space-y-16">
                    <x-room.description :room="$room" />
                    <x-room.facilities :room="$room" />
                    <x-room.score :room="$room" :breakdown="$scoreBreakdown" />
                    <x-room.map :room="$room" />
                    <x-room.pricing :room="$room" />
                </div>

            </div>

        </div>

    </section>

    <x-footer />

</x-layout>
