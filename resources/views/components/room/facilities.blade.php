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

    $specs = array_filter([
        ($room->surface_m2 ?? null) ? [
            'label' => 'Oppervlakte',
            'value' => $room->surface_m2 . ' m²',
            'icon'  => 'arrows-pointing-out',
        ] : null,
        ($room->type ?? null) ? [
            'label' => 'Type',
            'value' => $typeLabels[$room->type] ?? $room->type,
            'icon'  => 'home',
        ] : null,
        [
            'label' => 'Inrichting',
            'value' => ($room->is_furnished ?? false) ? 'Gemeubeld' : 'Ongemeubeld',
            'icon'  => 'archive-box',
        ],
($room->available_from ?? null) ? [
            'label' => 'Beschikbaar',
            'value' => $room->available_from->format('d/m/Y'),
            'icon'  => 'calendar-days',
        ] : null,
        ($room->deposit_amount ?? null) ? [
            'label' => 'Waarborg',
            'value' => '€' . number_format((float) $room->deposit_amount, 0, ',', '.'),
            'icon'  => 'shield-check',
        ] : null,
    ]);

    $facilitiesByCategory = $room->facilities?->groupBy('category') ?? collect();
@endphp

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> Eigenschappen & faciliteiten
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat zit er in dit kot?</h2>

    {{-- Spec-grid --}}
    @if (count($specs))
        <div class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-hairline bg-hairline sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($specs as $spec)
                <div class="flex flex-col gap-2.5 bg-canvas px-4 py-4 sm:px-5">
                    <x-dynamic-component :component="'heroicon-o-' . $spec['icon']" class="h-5 w-5 text-ink/35" aria-hidden="true" />
                    <div class="min-w-0">
                        <p class="truncate text-[0.58rem] font-medium uppercase tracking-[0.1em] text-ink/45">
                            {{ $spec['label'] }}
                        </p>
                        <p class="mt-0.5 truncate text-sm font-medium text-ink">
                            {{ $spec['value'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Faciliteiten van de verhuurder --}}
    @if ($facilitiesByCategory->isNotEmpty())
        <div class="mt-8 space-y-6">
            @foreach ($facilitiesByCategory as $category => $items)
                <div>
                    <p class="mb-3 text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/45">
                        {{ $category ?? 'Overige' }}
                    </p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($items as $facility)
                            <div class="flex items-start gap-3 rounded-xl border border-hairline bg-canvas-deep px-4 py-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-secondary-600/10">
                                    <x-heroicon-o-check class="h-3 w-3 text-secondary-600" aria-hidden="true" />
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
    @elseif (!count($specs))
        <p class="text-sm italic text-ink/40">Nog geen eigenschappen opgegeven.</p>
    @endif
</div>
