<x-filament-panels::page>
    @php
        $balance = $this->getBalance();
        $packs = $this->getPacks();
        $transactions = $this->getRecentTransactions();
        // "Beste waarde" = meeste credits per euro (uit de al opgehaalde collectie).
        $bestId = $packs->sortByDesc(fn ($p) => $p->price > 0 ? $p->credits / $p->price : 0)->first()?->id;
    @endphp

    {{-- Saldo --}}
    <div class="overflow-hidden rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 shadow-[0_8px_32px_rgba(0,16,30,0.06)]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Je saldo</p>
                <p class="mt-2 flex items-baseline gap-2">
                    <span class="text-[2.75rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ number_format($balance, 0, ',', '.') }}</span>
                    <span class="text-sm font-medium tracking-[-0.01em] text-[#586573]">credits</span>
                </p>
            </div>
            <p class="max-w-xs text-sm tracking-[-0.01em] text-[#586573]">
                Gebruik je credits om de gegevens van een verhuurder te ontgrendelen. Eén ontgrendeling geldt meteen voor al hun panden.
            </p>
        </div>
    </div>

    {{-- Bundels --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Koop credits</p>

        @if ($packs->isEmpty())
            <div class="mt-5 rounded-[1.25rem] border border-dashed border-[#0f17201f] bg-white p-8 text-center text-sm tracking-[-0.01em] text-[#586573]">
                Er zijn op dit moment geen bundels beschikbaar. Kom later nog eens terug.
            </div>
        @else
            <div class="mt-5 grid gap-5 sm:grid-cols-2 md:grid-cols-3 md:items-stretch">
                @foreach ($packs as $pack)
                    @php $isBest = $pack->id === $bestId && $packs->count() > 1; @endphp

                    <div @class([
                        'relative flex flex-col overflow-hidden rounded-[1.25rem] bg-white p-6 transition-shadow duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none',
                        'shadow-[0_10px_36px_rgba(0,16,30,0.10)] ring-1 ring-[#3a6ea5]' => $isBest,
                        'shadow-[0_8px_32px_rgba(0,16,30,0.06)] ring-1 ring-inset ring-[#0f17201f] hover:shadow-[0_12px_40px_rgba(0,16,30,0.10)]' => ! $isBest,
                    ])>
                        @if ($isBest)
                            <span class="absolute inset-x-0 top-0 h-[2px] bg-[#3a6ea5]" aria-hidden="true"></span>
                        @endif

                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="text-[1.25rem] font-medium leading-none tracking-[-0.02em] text-[#0f1720]">{{ $pack->name }}</h3>
                            @if ($isBest)
                                <span class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#3a6ea5]">Beste waarde</span>
                            @endif
                        </div>

                        <div class="mt-5 flex flex-1 items-baseline gap-2">
                            <span class="text-[2.25rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ number_format($pack->credits, 0, ',', '.') }}</span>
                            <span class="text-sm tracking-[-0.01em] text-[#586573]">credits</span>
                        </div>

                        <p class="mt-4 text-sm tracking-[-0.01em] text-[#586573]">
                            <span class="text-base font-medium tabular-nums text-[#0f1720]">&euro;&nbsp;{{ number_format($pack->price / 100, 2, ',', '.') }}</span>
                        </p>

                        <div class="mt-6">
                            <button type="button" wire:click="buy({{ $pack->id }})" wire:loading.attr="disabled" wire:target="buy({{ $pack->id }})"
                                class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#002f5b] px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] disabled:opacity-50 motion-reduce:transition-none">
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
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Recente activiteit</p>

            <ul class="mt-4 divide-y divide-[#0f17200f]">
                @foreach ($transactions as $transaction)
                    @php $positive = $transaction->amount > 0; @endphp
                    <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium tracking-[-0.01em] text-[#0f1720]">{{ $this->transactionLabel($transaction) }}</p>
                            <p class="text-xs tracking-[-0.01em] text-[#586573]">{{ $transaction->created_at?->locale('nl')->isoFormat('D MMMM YYYY') }}</p>
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
