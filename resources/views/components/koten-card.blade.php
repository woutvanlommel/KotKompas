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

<a href="{{ route('contact') }}"
   class="group flex flex-col overflow-hidden rounded-xl border border-base-twee-300 bg-white transition duration-200 hover:-translate-y-1 hover:border-primary-300 hover:shadow-lg">
    <div class="aspect-[4/3] overflow-hidden bg-base-een-300">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-base-twee-600">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-medium text-base-een-700">{{ $room->building?->city }}</p>
            <span class="shrink-0 rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700">{{ $type }}</span>
        </div>

        <p class="mt-1 truncate font-semibold text-primary-900">{{ $room->title ?: $type }}</p>

        <p class="mt-2 text-lg font-semibold text-primary-900">
            €{{ number_format((float) $room->price_per_month, 0, ',', '.') }}<span class="text-sm font-normal text-base-een-700">&nbsp;/maand</span>
        </p>

        <div class="mt-3 flex flex-wrap gap-1.5 text-xs text-base-een-700">
            @if ($room->surface_m2)
                <span class="rounded bg-base-een-200 px-2 py-1">{{ $room->surface_m2 }} m²</span>
            @endif
            <span class="rounded bg-base-een-200 px-2 py-1">{{ $room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld' }}</span>
            @if ($room->available_from)
                <span class="rounded bg-base-een-200 px-2 py-1">Vanaf {{ $room->available_from->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>
</a>
