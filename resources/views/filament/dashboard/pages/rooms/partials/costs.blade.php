@php
    $costTypes = $room->costTypes;

    $monthly  = $costTypes->where('pivot.frequency', 'monthly');
    $yearly   = $costTypes->where('pivot.frequency', 'yearly');
    $oneTime  = $costTypes->where('pivot.frequency', 'one_time');
@endphp

<div class="bg-white rounded-[1.25rem] border border-[#0f17201f] p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-medium tracking-[-0.01em] text-[#0f1720]">Prijsoverzicht</h2>
        <button wire:click="mountAction('editCosts')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[4px] text-xs font-medium text-[#586573] hover:text-[#0f1720] hover:bg-[#e1e6ed] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Bewerken
        </button>
    </div>

    <dl class="space-y-3">

        {{-- Basishuur --}}
        <div class="flex items-center justify-between">
            <dt class="text-sm text-[#586573]">Basishuur</dt>
            <dd class="text-sm font-medium text-[#0f1720]">€ {{ number_format($room->price_per_month, 2, ',', '.') }} / maand</dd>
        </div>

        {{-- Voorschot --}}
        @if ($room->deposit_amount)
            <div class="flex items-center justify-between">
                <dt class="text-sm text-[#586573]">Voorschot (eenmalig)</dt>
                <dd class="text-sm font-medium text-[#0f1720]">€ {{ number_format($room->deposit_amount, 2, ',', '.') }}</dd>
            </div>
        @endif

        {{-- Maandelijkse kosten --}}
        @if ($monthly->isNotEmpty())
            <div class="pt-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#9aa6b4] mb-2">Maandelijks</p>
                <div class="space-y-2">
                    @foreach ($monthly as $cost)
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-[#586573] flex items-center gap-1.5">
                                {{ $cost->name }}
                                @if ($cost->pivot->description)
                                    <span class="text-[#9aa6b4] text-xs">({{ $cost->pivot->description }})</span>
                                @endif
                            </dt>
                            <dd class="text-sm font-medium text-[#0f1720]">
                                @if ($cost->pivot->is_variable)
                                    <span class="text-[#c2510a]">Variabel</span>
                                @elseif ($cost->pivot->amount)
                                    € {{ number_format($cost->pivot->amount, 2, ',', '.') }} / maand
                                @else
                                    <span class="text-[#9aa6b4]">—</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Jaarlijkse kosten --}}
        @if ($yearly->isNotEmpty())
            <div class="pt-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#9aa6b4] mb-2">Jaarlijks</p>
                <div class="space-y-2">
                    @foreach ($yearly as $cost)
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-[#586573] flex items-center gap-1.5">
                                {{ $cost->name }}
                                @if ($cost->pivot->description)
                                    <span class="text-[#9aa6b4] text-xs">({{ $cost->pivot->description }})</span>
                                @endif
                            </dt>
                            <dd class="text-sm font-medium text-[#0f1720]">
                                @if ($cost->pivot->is_variable)
                                    <span class="text-[#c2510a]">Variabel</span>
                                @elseif ($cost->pivot->amount)
                                    € {{ number_format($cost->pivot->amount, 2, ',', '.') }} / jaar
                                @else
                                    <span class="text-[#9aa6b4]">—</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Eenmalige kosten --}}
        @if ($oneTime->isNotEmpty())
            <div class="pt-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#9aa6b4] mb-2">Eenmalig</p>
                <div class="space-y-2">
                    @foreach ($oneTime as $cost)
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-[#586573] flex items-center gap-1.5">
                                {{ $cost->name }}
                                @if ($cost->pivot->description)
                                    <span class="text-[#9aa6b4] text-xs">({{ $cost->pivot->description }})</span>
                                @endif
                            </dt>
                            <dd class="text-sm font-medium text-[#0f1720]">
                                @if ($cost->pivot->is_variable)
                                    <span class="text-[#c2510a]">Variabel</span>
                                @elseif ($cost->pivot->amount)
                                    € {{ number_format($cost->pivot->amount, 2, ',', '.') }}
                                @else
                                    <span class="text-[#9aa6b4]">—</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Totaal maandelijks --}}
        <div class="border-t border-[#0f17201f] pt-4 flex items-center justify-between">
            <dt class="text-sm font-semibold text-[#0f1720]">
                Totaal / maand
                <span class="text-xs font-normal text-[#9aa6b4]">(excl. variabele kosten)</span>
            </dt>
            <dd class="text-base font-bold text-[#0f1720]">
                € {{ number_format($room->total_monthly_price, 2, ',', '.') }}
            </dd>
        </div>

    </dl>
</div>
