@props(['room'])

@php
    $typeLabels = [
        'studio' => 'Studio',
        'one_bedroom' => '1 slaapkamer',
        'two_bedroom' => '2 slaapkamers',
        'three_bedroom' => '3 slaapkamers',
        'four_bedroom' => '4 slaapkamers',
        'five_plus_bedroom' => '5+ slaapkamers',
    ];
    $type = $typeLabels[$room->type] ?? $room->type;
    $photo = $room->getFirstMediaUrl('rooms') ?: null;
    $city = $room->building?->city ?? 'Kot';
    $price = number_format((float) $room->price_per_month, 0, ',', '.');

    $tags = array_filter([
        $room->surface_m2 ? $room->surface_m2 . ' m²' : null,
        $room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld',
        $room->available_from ? 'Vanaf ' . $room->available_from->format('m/Y') : null,
    ]);
@endphp

{{-- Lijst-rij: editorial index-rij — foto links, stad + specs midden, prijs rechts.
     Hairline-scheiding, hover schuift de pijl in beeld. --}}
<a href="{{ route('rooms.show', $room) }}"
   class="group flex items-center gap-5 border-t border-hairline py-4 transition-colors duration-200 last:border-b hover:bg-canvas-deep/50 sm:gap-7">

    <span class="relative block h-20 w-28 shrink-0 overflow-hidden rounded-xl bg-primary-900 sm:h-24 sm:w-36">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition-transform duration-600 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.05]">
        @else
            <span class="absolute inset-0 flex items-center justify-center bg-primary-800 text-white/15">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                    <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        @endif
    </span>

    <span class="min-w-0 flex-1">
        <span class="block truncate text-lg font-medium leading-tight tracking-[-0.02em] text-ink">{{ $city }}</span>
        <span class="mt-0.5 block text-[0.65rem] font-medium uppercase tracking-[0.14em] text-ink-soft">{{ $type }}</span>
        @if ($tags)
            <span class="mt-2 hidden flex-wrap gap-x-4 gap-y-1 text-[0.8rem] text-ink-soft sm:flex">
                @foreach ($tags as $tag)
                    <span class="flex items-center gap-2"><span class="inline-block h-1 w-1 rounded-full bg-accent-500"></span>{{ $tag }}</span>
                @endforeach
            </span>
        @endif
    </span>

    <span class="flex shrink-0 items-center gap-4 pr-1 sm:gap-6">
        <x-score-badge :score="$room->score" :count="$room->reviews_count" class="hidden sm:inline-flex" />
        <span class="text-lg font-medium tabular-nums text-ink">€{{ $price }}<span class="ml-0.5 align-top text-[0.6rem] text-ink-soft">/m</span></span>
        <span class="hidden text-ink-soft transition-all duration-300 group-hover:translate-x-1 group-hover:text-secondary-600 sm:block" aria-hidden="true">→</span>
    </span>
</a>
