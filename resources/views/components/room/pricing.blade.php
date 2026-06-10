@props(['room'])

@php
    $costTypes   = $room->costTypes ?? collect();
    $monthly     = $costTypes->where('pivot.frequency', 'monthly');
    $yearly      = $costTypes->where('pivot.frequency', 'yearly');
    $oneTime     = $costTypes->where('pivot.frequency', 'one_time');
    $hasExtra           = $yearly->isNotEmpty() || $oneTime->isNotEmpty();
    $hasVariable        = $costTypes->where('pivot.is_variable', true)->isNotEmpty();
    $hasMonthlyVariable = $monthly->where('pivot.is_variable', true)->isNotEmpty();
@endphp

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> Kosten
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Prijsoverzicht</h2>

    <div class="overflow-hidden rounded-2xl border border-hairline">
        <dl class="divide-y divide-hairline">

            {{-- Basishuur --}}
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-4">
                <dt class="text-sm text-ink/70">Basishuur</dt>
                <dd class="text-sm font-medium tabular-nums text-ink">
                    €{{ number_format((float) ($room->price_per_month ?? 0), 2, ',', '.') }}
                    <span class="text-xs font-normal text-ink/45">/ maand</span>
                </dd>
            </div>

            {{-- Waarborg --}}
            @if ($room->deposit_amount ?? null)
                <div class="flex items-center justify-between gap-3 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-4">
                    <dt class="text-sm text-ink/70">Waarborg</dt>
                    <dd class="text-sm font-medium tabular-nums text-ink">
                        €{{ number_format((float) $room->deposit_amount, 2, ',', '.') }}
                        <span class="text-xs font-normal text-ink/45">eenmalig</span>
                    </dd>
                </div>
            @endif

            {{-- Maandelijkse kosten --}}
            @if ($monthly->isNotEmpty())
                <div class="bg-canvas-deep px-4 py-2.5 sm:px-6 sm:py-3">
                    <p class="text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Maandelijks</p>
                </div>
                @foreach ($monthly as $cost)
                    <div class="flex items-center justify-between gap-3 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-4">
                        <dt class="flex flex-wrap items-center gap-1.5 text-sm text-ink/70">
                            {{ $cost->name ?? '—' }}
                            @if ($cost->pivot?->description ?? null)
                                <span class="text-xs text-ink/40">({{ $cost->pivot->description }})</span>
                            @endif
                        </dt>
                        <dd class="shrink-0 text-sm font-medium tabular-nums">
                            @if ($cost->pivot?->is_variable ?? false)
                                <span class="text-amber-600">Variabel *</span>
                            @elseif ($cost->pivot?->amount ?? null)
                                <span class="text-ink">€{{ number_format((float) $cost->pivot->amount, 2, ',', '.') }}</span>
                                <span class="text-xs font-normal text-ink/45">/ maand</span>
                            @else
                                <span class="text-ink/30">Nader te bepalen</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            @endif

            {{-- Totaal / maand --}}
            <div class="flex items-center justify-between gap-3 bg-primary-900 px-4 py-4 sm:px-6 sm:py-5">
                <dt class="text-sm font-medium text-white/80">
                    Totaal / maand
                    @if ($hasMonthlyVariable)
                        <span class="block text-[0.7rem] font-normal text-white/45">excl. variabele kosten</span>
                    @endif
                </dt>
                <dd class="text-xl font-medium tabular-nums text-white">
                    €{{ number_format((float) ($room->total_monthly_price ?? $room->price_per_month ?? 0), 2, ',', '.') }}
                </dd>
            </div>

            {{-- Jaarlijkse kosten --}}
            @if ($yearly->isNotEmpty())
                <div class="bg-canvas-deep px-4 py-2.5 sm:px-6 sm:py-3">
                    <p class="text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Jaarlijks</p>
                </div>
                @foreach ($yearly as $cost)
                    <div class="flex items-center justify-between gap-3 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-4">
                        <dt class="flex flex-wrap items-center gap-1.5 text-sm text-ink/70">
                            {{ $cost->name ?? '—' }}
                            @if ($cost->pivot?->description ?? null)
                                <span class="text-xs text-ink/40">({{ $cost->pivot->description }})</span>
                            @endif
                        </dt>
                        <dd class="shrink-0 text-sm font-medium tabular-nums">
                            @if ($cost->pivot?->is_variable ?? false)
                                <span class="text-amber-600">Variabel *</span>
                            @elseif ($cost->pivot?->amount ?? null)
                                <span class="text-ink">€{{ number_format((float) $cost->pivot->amount, 2, ',', '.') }}</span>
                                <span class="text-xs font-normal text-ink/45">/ jaar</span>
                            @else
                                <span class="text-ink/30">Nader te bepalen</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            @endif

            {{-- Eenmalige kosten --}}
            @if ($oneTime->isNotEmpty())
                <div class="bg-canvas-deep px-4 py-2.5 sm:px-6 sm:py-3">
                    <p class="text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Eenmalig</p>
                </div>
                @foreach ($oneTime as $cost)
                    <div class="flex items-center justify-between gap-3 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-4">
                        <dt class="flex flex-wrap items-center gap-1.5 text-sm text-ink/70">
                            {{ $cost->name ?? '—' }}
                            @if ($cost->pivot?->description ?? null)
                                <span class="text-xs text-ink/40">({{ $cost->pivot->description }})</span>
                            @endif
                        </dt>
                        <dd class="shrink-0 text-sm font-medium tabular-nums">
                            @if ($cost->pivot?->is_variable ?? false)
                                <span class="text-amber-600">Variabel *</span>
                            @elseif ($cost->pivot?->amount ?? null)
                                <span class="text-ink">€{{ number_format((float) $cost->pivot->amount, 2, ',', '.') }}</span>
                            @else
                                <span class="text-ink/30">Nader te bepalen</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            @endif

        </dl>
    </div>

    @if ($hasVariable)
        <p class="mt-3 text-xs text-ink/45">
            * Variabele kosten hebben geen vaste prijs en worden apart afgerekend op basis van de werkelijke kost.
        </p>
    @endif
</div>
