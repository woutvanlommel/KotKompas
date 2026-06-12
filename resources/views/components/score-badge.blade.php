@props(['score' => null, 'count' => 0, 'variant' => 'light'])

{{-- Kotscore-pill: rendert niets zolang er geen beoordelingen zijn. Toont de
     cached weergavescore (nooit score_bayesian — die is alleen voor ranking).
     variant="dark" volgt de chip-taal van de koten-kaarten (wit op foto). --}}
@if ($score !== null && $count > 0)
    @if ($variant === 'dark')
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border border-white/20 bg-white/10 px-2 py-0.5 text-[0.65rem] text-white backdrop-blur-sm']) }}>
            <x-heroicon-s-star class="h-3 w-3 shrink-0" aria-hidden="true" />
            <span class="font-semibold tabular-nums">{{ number_format($score, 1, ',', '.') }}</span>
            <span class="text-white/60">({{ $count }})</span>
        </span>
    @else
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border border-hairline bg-canvas-deep px-3 py-1 text-[0.7rem] font-medium uppercase tracking-[0.1em] text-ink/70']) }}>
            <x-heroicon-s-star class="h-3.5 w-3.5 shrink-0 text-secondary-600" aria-hidden="true" />
            <span class="font-semibold text-ink">{{ number_format($score, 1, ',', '.') }}</span>
            <span aria-hidden="true">·</span>
            {{ $count }} {{ $count === 1 ? 'beoordeling' : 'beoordelingen' }}
        </span>
    @endif
@endif
