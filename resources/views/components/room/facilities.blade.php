@props(['room'])

@php
    $facilitiesByCategory = $room->facilities?->groupBy('category') ?? collect();

    $baseItems = [
        ['label' => 'Gemeubeld',         'present' => (bool) ($room->is_furnished ?? false)],
        ['label' => 'Kosten inbegrepen', 'present' => (bool) ($room->costs_included ?? false)],
    ];
@endphp

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> Faciliteiten
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat is er aanwezig?</h2>

    {{-- Basisitems --}}
    <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ($baseItems as $item)
            <div class="flex items-center gap-3 rounded-xl border border-hairline bg-canvas-deep px-4 py-3">
                @if ($item['present'])
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-secondary-600/10">
                        <svg class="h-3.5 w-3.5 text-secondary-600" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M3 8.5l3.5 3.5L13 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                @else
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink/5">
                        <svg class="h-3.5 w-3.5 text-ink/30" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                @endif
                <span class="text-sm {{ $item['present'] ? 'text-ink' : 'text-ink/40 line-through' }}">
                    {{ $item['label'] }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- Verhuurder-faciliteiten per categorie --}}
    @if ($facilitiesByCategory->isNotEmpty())
        <div class="space-y-6">
            @foreach ($facilitiesByCategory as $category => $items)
                <div>
                    <p class="mb-3 text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">
                        {{ $category ?? 'Overige' }}
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($items as $facility)
                            <span class="inline-flex items-center gap-2 rounded-xl border border-hairline bg-canvas-deep px-3.5 py-2 text-sm text-ink">
                                <svg class="h-3.5 w-3.5 shrink-0 text-secondary-600" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M3 8.5l3.5 3.5L13 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ $facility->name ?? '—' }}
                                @if ($facility->pivot?->description ?? null)
                                    <span class="text-ink/45">— {{ $facility->pivot->description }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm italic text-ink/40">De verhuurder heeft nog geen extra faciliteiten opgegeven.</p>
    @endif
</div>
