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
@endphp

<a href="{{ route('contact') }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-canvas-deep">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.04]">
        @else
            <div class="flex h-full w-full items-center justify-center text-ink/20">
                <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                    <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif
        <span class="absolute left-3 top-3 bg-canvas/90 px-2 py-1 text-[0.62rem] font-medium uppercase tracking-[0.14em] text-ink backdrop-blur-sm">{{ $type }}</span>
    </div>

    <div class="mt-4 flex items-baseline justify-between gap-3 border-t border-hairline pt-3">
        <p class="text-sm font-medium tracking-tight text-ink">{{ $room->building?->city }}</p>
        <p class="text-sm font-medium tracking-tight text-ink">
            €{{ number_format((float) $room->price_per_month, 0, ',', '.') }}<span class="text-ink-soft">/m</span>
        </p>
    </div>

    @if ($room->title)
        <p class="mt-1 truncate text-sm text-ink-soft">{{ $room->title }}</p>
    @endif

    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[0.7rem] uppercase tracking-[0.1em] text-ink-soft">
        @if ($room->surface_m2)<span>{{ $room->surface_m2 }} m²</span><span class="text-hairline">·</span>@endif
        <span>{{ $room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld' }}</span>
        @if ($room->available_from)<span class="text-hairline">·</span><span>Vanaf {{ $room->available_from->format('m/Y') }}</span>@endif
    </div>
</a>
