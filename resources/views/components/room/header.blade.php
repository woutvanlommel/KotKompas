@props(['room'])

@php
    $typeLabels = [
        'studio'            => 'Studio',
        'one_bedroom'       => '1 slaapkamer',
        'two_bedroom'       => '2 slaapkamers',
        'three_bedroom'     => '3 slaapkamers',
        'four_bedroom'      => '4 slaapkamers',
        'five_plus_bedroom' => '5+ slaapkamers',
    ];
    $typeLabel = $typeLabels[$room->type ?? ''] ?? ($room->type ?? null);

    $badges = array_filter([
        $typeLabel,
        ($room->surface_m2 ?? null) ? $room->surface_m2 . ' m²' : null,
        isset($room->is_furnished) ? ($room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld') : null,
        ($room->available_from ?? null) ? 'Vrij vanaf ' . $room->available_from->format('d/m/Y') : null,
    ]);
@endphp

<div class="flex flex-wrap items-start justify-between gap-4">

    <div class="min-w-0">
        <h1 class="text-[clamp(2rem,4vw,3.5rem)] font-medium leading-[0.9] tracking-[-0.04em]">
            {{ $room->title ?? 'Kot' }}
        </h1>
        <p class="mt-3 inline-flex items-center gap-2 text-sm text-ink/60">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M8 14.5s5-4 5-7.5a5 5 0 1 0-10 0c0 3.5 5 7.5 5 7.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                <circle cx="8" cy="7" r="1.5" fill="currentColor"/>
            </svg>
            {{ $room->building?->full_address ?? '—' }}
        </p>
        @if (count($badges))
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($badges as $badge)
                    <span class="inline-block rounded-full border border-hairline bg-canvas-deep px-3 py-1 text-[0.7rem] font-medium uppercase tracking-[0.1em] text-ink/70">
                        {{ $badge }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="shrink-0 text-right">
        <p class="text-[clamp(1.8rem,3vw,2.8rem)] font-medium leading-none tracking-[-0.04em]">
            €{{ number_format((float) ($room->price_per_month ?? 0), 0, ',', '.') }}
        </p>
        <p class="mt-1 text-sm text-ink/55">per maand</p>
        @if ($room->costs_included ?? false)
            <p class="mt-1 text-xs text-secondary-600">Kosten inbegrepen</p>
        @endif
    </div>

</div>
