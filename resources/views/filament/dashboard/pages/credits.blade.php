<x-filament-panels::page>
    @php
        $balance = $this->getBalance();
        $packs = $this->getPacks();
        $transactions = $this->getRecentTransactions();
    @endphp

    {{-- Saldo --}}
    <div class="relative overflow-hidden rounded-[1.25rem] border border-[#0f17201f] bg-gradient-to-br from-white to-[#f4f7fb] p-6 shadow-[0_8px_32px_rgba(0,16,30,0.06)] sm:p-7">
        {{-- Decoratieve hoek-accent --}}
        <span class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[#3a6ea5]/[0.06]" aria-hidden="true"></span>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[0.875rem] bg-[#002f5b] text-white shadow-[0_4px_14px_rgba(0,47,91,0.25)]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9V6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25V17.25A2.25 2.25 0 0119.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V9zm15 4.5h.008v.008H17.25V13.5z"/></svg>
                </span>
                <div>
                    <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Je saldo</p>
                    <p class="mt-1 flex items-baseline gap-2">
                        <span class="text-[2.75rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ number_format($balance, 0, ',', '.') }}</span>
                        <span class="text-sm font-medium tracking-[-0.01em] text-[#586573]">credits</span>
                    </p>
                </div>
            </div>
            <p class="max-w-xs text-sm leading-relaxed tracking-[-0.01em] text-[#586573]">
                Gebruik je credits om de gegevens van een verhuurder te ontgrendelen. Eén ontgrendeling geldt meteen voor al hun panden.
            </p>
        </div>
    </div>

    {{-- Bundels --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Koop credits</p>

        @if ($packs->isEmpty())
            <div class="mt-5 rounded-[1.25rem] border border-dashed border-[#0f172033] bg-white p-10 text-center text-sm tracking-[-0.01em] text-[#586573]">
                Er zijn op dit moment geen bundels beschikbaar. Kom later nog eens terug.
            </div>
        @else
            <div class="mt-5 grid gap-5 sm:grid-cols-2 md:grid-cols-3 md:items-stretch">
                @foreach ($packs as $pack)
                    @php
                        $isFeatured = $pack->is_featured;
                        $perCredit = $pack->credits > 0 ? $pack->price / $pack->credits : null;
                    @endphp

                    <div @class([
                        'group relative flex flex-col overflow-hidden rounded-[1.25rem] bg-white p-6 transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none',
                        'shadow-[0_10px_36px_rgba(0,16,30,0.10)] ring-1 ring-[#3a6ea5]' => $isFeatured,
                        'shadow-[0_8px_32px_rgba(0,16,30,0.06)] ring-1 ring-inset ring-[#0f17201f] hover:-translate-y-0.5 hover:shadow-[0_14px_44px_rgba(0,16,30,0.12)]' => ! $isFeatured,
                    ])>
                        @if ($isFeatured)
                            <span class="absolute inset-x-0 top-0 h-[2px] bg-[#3a6ea5]" aria-hidden="true"></span>
                        @endif

                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="text-[1.25rem] font-medium leading-none tracking-[-0.02em] text-[#0f1720]">{{ $pack->name }}</h3>
                            @if ($isFeatured)
                                <span class="shrink-0 rounded-full bg-[#3a6ea5]/[0.1] px-2.5 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#3a6ea5]">Aanbevolen</span>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-1 items-baseline gap-2">
                            <span class="text-[2.5rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ number_format($pack->credits, 0, ',', '.') }}</span>
                            <span class="text-sm tracking-[-0.01em] text-[#586573]">credits</span>
                        </div>

                        <div class="mt-4 flex items-baseline justify-between border-t border-[#0f17200f] pt-4">
                            <span class="text-base font-medium tabular-nums text-[#0f1720]">&euro;&nbsp;{{ number_format($pack->price / 100, 2, ',', '.') }}</span>
                            @if ($perCredit)
                                <span class="text-xs tracking-[-0.01em] text-[#8893a0]">&euro;&nbsp;{{ number_format($perCredit / 100, 2, ',', '.') }} / credit</span>
                            @endif
                        </div>

                        <div class="mt-6">
                            <button type="button" wire:click="buy({{ $pack->id }})" wire:loading.attr="disabled" wire:target="buy({{ $pack->id }})" @class([
                                'inline-flex h-11 w-full items-center justify-center rounded-[4px] px-5 text-xs font-medium uppercase tracking-[0.04em] transition-colors duration-300 disabled:opacity-50 motion-reduce:transition-none',
                                'bg-[#002f5b] text-white hover:bg-[#001f3d]' => $isFeatured,
                                'bg-[#0f1720] text-white hover:bg-[#002f5b]' => ! $isFeatured,
                            ])>
                                <span wire:loading.remove wire:target="buy({{ $pack->id }})">Koop</span>
                                <span wire:loading wire:target="buy({{ $pack->id }})">Bezig…</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 text-xs tracking-[-0.01em] text-[#586573]">Betaling verloopt via Stripe — je ziet het bedrag bij het afrekenen. Credits zijn los niet te koop, enkel per bundel.</p>
        @endif
    </div>

    {{-- Recente activiteit --}}
    @if ($transactions->isNotEmpty())
        <div class="rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 shadow-[0_8px_32px_rgba(0,16,30,0.06)]">
            <div class="flex items-center justify-between gap-3">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Recente activiteit</p>
                @if ($this->hasMoreTransactions())
                    <a href="{{ $this->historyUrl() }}" class="inline-flex items-center gap-1 text-xs font-medium tracking-[-0.01em] text-[#3a6ea5] transition-colors hover:text-[#002f5b]">
                        Bekijk alles
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>

            <ul class="mt-4 divide-y divide-[#0f17200f]">
                @foreach ($transactions as $transaction)
                    @php $positive = $transaction->amount > 0; @endphp
                    <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span @class([
                                'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                                'bg-[#1f7a4d]/[0.1] text-[#1f7a4d]' => $positive,
                                'bg-[#0f1720]/[0.06] text-[#586573]' => ! $positive,
                            ])>
                                @if ($positive)
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium tracking-[-0.01em] text-[#0f1720]">{{ $transaction->label() }}</p>
                                <p class="text-xs tracking-[-0.01em] text-[#586573]">{{ $transaction->created_at?->locale('nl')->isoFormat('D MMMM YYYY') }}</p>
                            </div>
                        </div>
                        <span @class([
                            'shrink-0 text-sm font-medium tabular-nums',
                            'text-[#1f7a4d]' => $positive,
                            'text-[#586573]' => ! $positive,
                        ])>{{ $positive ? '+' : '' }}{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-filament-panels::page>
