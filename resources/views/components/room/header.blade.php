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

    $basePrice  = (float) ($room->price_per_month ?? 0);
    $totalPrice = (float) ($room->total_monthly_price ?? $basePrice);
    $extraCosts = round($totalPrice - $basePrice, 2);

    // Vaste maandelijkse kosten naast de basishuur
    $fixedMonthlyCosts = ($room->costTypes ?? collect())
        ->where('pivot.frequency', 'monthly')
        ->where('pivot.is_variable', false)
        ->whereNotNull('pivot.amount');

    // Kosten inbegrepen: enkel "inbegrepen" als costs_included=true én er geen aparte maandkost staat
    $costsIncluded = ($room->costs_included ?? false) && $fixedMonthlyCosts->isEmpty();
@endphp

<div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4">

    {{-- Titel + adres --}}
    <div class="min-w-0 flex-1">
        <h1 class="text-[clamp(1.8rem,4vw,3.5rem)] font-medium leading-[0.9] tracking-[-0.04em]">
            {{ $room->title ?? 'Kot' }}
        </h1>
        <p class="mt-3 inline-flex items-center gap-1.5 text-sm text-ink/60">
            <x-heroicon-o-map-pin class="h-4 w-4 shrink-0" aria-hidden="true" />
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

    {{-- Prijs --}}
    <div class="shrink-0 text-right">
        <p class="text-[clamp(1.6rem,3vw,2.8rem)] font-medium leading-none tracking-[-0.04em]">
            €{{ number_format($totalPrice, 0, ',', '.') }}
        </p>
        <p class="mt-1 text-sm text-ink/55">per maand</p>

        {{-- Uitsplitsing als er vaste extra kosten zijn --}}
        @if ($extraCosts > 0)
            <p class="mt-1.5 text-xs text-ink/50">
                €{{ number_format($basePrice, 0, ',', '.') }} huur
                + €{{ number_format($extraCosts, 0, ',', '.') }} vaste kosten
            </p>
        @endif

        {{-- Badge: kosten inbegrepen of niet --}}
        @if ($costsIncluded)
            <span class="mt-2 inline-block rounded-full bg-secondary-600/10 px-2.5 py-1 text-xs font-medium text-secondary-600">
                Kosten inbegrepen
            </span>
        @elseif (!$fixedMonthlyCosts->isEmpty())
            <span class="mt-2 inline-block rounded-full bg-ink/5 px-2.5 py-1 text-xs font-medium text-ink/50">
                Excl. variabelen kosten
            </span>
        @else
            <span class="mt-2 inline-block rounded-full bg-ink/5 px-2.5 py-1 text-xs font-medium text-ink/50">
                Kosten niet inbegrepen
            </span>
        @endif
    </div>

</div>
