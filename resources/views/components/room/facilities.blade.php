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

    // Specs die altijd getoond worden als ze een waarde hebben
    $specs = array_filter([
        $room->surface_m2 ? [
            'label' => 'Oppervlakte',
            'value' => $room->surface_m2 . ' m²',
            'icon'  => '<path d="M3 3h10v10H3V3Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M3 13l10-10" stroke="currentColor" stroke-width="1.3"/><path d="M13 3v4M3 13h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>',
        ] : null,
        $room->type ? [
            'label' => 'Type woning',
            'value' => $typeLabels[$room->type] ?? $room->type,
            'icon'  => '<path d="M2 7.5 8 2l6 5.5V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M5.5 15V9.5h5V15" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>',
        ] : null,
        [
            'label' => 'Inrichting',
            'value' => ($room->is_furnished ?? false) ? 'Gemeubeld' : 'Ongemeubeld',
            'icon'  => '<path d="M2 10h12M3 10V7.5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2V10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 10v3M14 10v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>',
        ],
        [
            'label' => 'Maandelijkse kosten',
            'value' => ($room->costs_included ?? false) ? 'Inbegrepen in huur' : 'Niet inbegrepen',
            'icon'  => '<circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v1.5M8 9.5V11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M6 7.5a2 2 0 1 1 2 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>',
        ],
        $room->available_from ? [
            'label' => 'Beschikbaar',
            'value' => $room->available_from->format('d/m/Y'),
            'icon'  => '<rect x="2" y="3.5" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M2 7h12M5.5 2v3M10.5 2v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>',
        ] : null,
        $room->deposit_amount ? [
            'label' => 'Waarborg',
            'value' => '€' . number_format((float) $room->deposit_amount, 0, ',', '.'),
            'icon'  => '<path d="M8 1.5 14 4v4c0 3.5-2.5 5.5-6 6.5C2.5 13.5 2 11 2 8V4l6-2.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>',
        ] : null,
    ]);

    $facilitiesByCategory = $room->facilities?->groupBy('category') ?? collect();
@endphp

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> Eigenschappen & faciliteiten
    </p>
    <h2 class="mb-8 text-xl font-medium tracking-[-0.02em]">Wat zit er in dit kot?</h2>

    {{-- Spec-grid: vaste eigenschappen van het Room model --}}
    <div class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-hairline bg-hairline sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($specs as $spec)
            <div class="flex flex-col gap-2 bg-canvas px-5 py-4">
                <svg class="h-5 w-5 text-ink/40" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    {!! $spec['icon'] !!}
                </svg>
                <div>
                    <p class="text-[0.6rem] font-medium uppercase tracking-[0.12em] text-ink/45">{{ $spec['label'] }}</p>
                    <p class="mt-0.5 text-sm font-medium text-ink">{{ $spec['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Faciliteiten van de verhuurder --}}
    @if ($facilitiesByCategory->isNotEmpty())
        <div class="mt-10 space-y-7">
            @foreach ($facilitiesByCategory as $category => $items)
                <div>
                    <p class="mb-3 text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/45">
                        {{ $category ?? 'Overige' }}
                    </p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($items as $facility)
                            <div class="flex items-center gap-3 rounded-xl border border-hairline bg-canvas-deep px-4 py-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-secondary-600/10">
                                    <svg class="h-3.5 w-3.5 text-secondary-600" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="M3 8.5l3.5 3.5L13 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm text-ink">{{ $facility->name ?? '—' }}</span>
                                    @if ($facility->pivot?->description ?? null)
                                        <span class="block text-xs text-ink/45">{{ $facility->pivot->description }}</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @elseif (count($specs) === 0)
        <p class="mt-6 text-sm italic text-ink/40">Nog geen eigenschappen opgegeven.</p>
    @endif
</div>
