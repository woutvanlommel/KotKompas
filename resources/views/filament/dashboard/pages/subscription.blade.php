<x-filament-panels::page>
    @php
        $plans = $this->getPlans();
        $subscribed = $this->isSubscribed();
        $subscription = $this->currentSubscription();
        $currentPriceId = $this->currentPriceId();
        $pendingPlan = $this->pendingPlan();
        $renewal = $this->getRenewalDate()?->locale('nl');
        $recommended = 'pro';
    @endphp

    {{-- Huidig abonnement --}}
    @if ($subscribed && $subscription)
        @php $grace = $subscription->onGracePeriod(); @endphp
        <div class="rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 shadow-[0_8px_32px_rgba(0,16,30,0.06)]">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Je huidige abonnement</p>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1 text-sm tracking-[-0.01em]">
                    @if ($grace)
                        <p class="font-medium text-[#c2510a]">
                            Opgezegd — verloopt op {{ $renewal?->isoFormat('D MMMM YYYY') }}
                            @if ($renewal)<span class="text-[#586573]">({{ $renewal->diffForHumans() }})</span>@endif
                        </p>
                        <p class="text-[#586573]">Je behoudt toegang tot die datum.</p>
                    @else
                        <p class="text-base font-medium tracking-[-0.01em] text-[#0f1720]">Je abonnement is actief.</p>
                        @if ($renewal)
                            <p class="text-[#586573]">Verlengt op {{ $renewal->isoFormat('D MMMM YYYY') }} <span class="tabular-nums">({{ $renewal->diffForHumans() }})</span>.</p>
                        @endif
                    @endif
                </div>

                <div class="flex shrink-0 gap-2">
                    @if ($grace)
                        <x-filament::button color="success" wire:click="mountAction('resume')">Hervatten</x-filament::button>
                    @else
                        <x-filament::button color="danger" outlined wire:click="mountAction('cancel')">Opzeggen</x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Geplande wijziging --}}
            @if ($pendingPlan)
                <div class="mt-5 flex flex-col gap-3 rounded-[0.75rem] border border-[#0f17201f] bg-[#eaf1f8] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-2.5 text-sm tracking-[-0.01em] text-[#2e5884]">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Wijziging gepland naar <strong class="font-semibold">{{ $pendingPlan->name }}</strong>@if ($renewal) — gaat in op {{ $renewal->isoFormat('D MMMM YYYY') }} ({{ $renewal->diffForHumans() }})@endif</span>
                    </div>
                    <x-filament::button size="sm" color="gray" wire:click="mountAction('cancelPendingSwap')">Wijziging annuleren</x-filament::button>
                </div>
            @endif
        </div>
    @endif

    {{-- Plannen --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">{{ $subscribed ? 'Wissel van plan' : 'Kies je plan' }}</p>

        <div class="mt-5 grid gap-5 md:grid-cols-3 md:items-stretch">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $currentPriceId !== null && $currentPriceId === $plan->priceId();
                    $isPending = $pendingPlan && $pendingPlan->id === $plan->id;
                    $isRec = $plan->slug === $recommended && ! $isCurrent && ! $isPending;
                @endphp

                <div @class([
                    'relative flex flex-col overflow-hidden rounded-[1.25rem] bg-white p-6 transition-shadow duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none',
                    'shadow-[0_10px_36px_rgba(0,16,30,0.10)] ring-1 ring-[#3a6ea5]' => $isCurrent,
                    'shadow-[0_8px_32px_rgba(0,16,30,0.06)] ring-1 ring-inset ring-[#0f17201f] hover:shadow-[0_12px_40px_rgba(0,16,30,0.10)]' => ! $isCurrent,
                ])>
                    {{-- Aanbevolen: cerulean hairline-accent bovenaan (geen "most popular"-pill) --}}
                    @if ($isRec)
                        <span class="absolute inset-x-0 top-0 h-[2px] bg-[#3a6ea5]" aria-hidden="true"></span>
                    @endif

                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="text-[1.5rem] font-medium leading-none tracking-[-0.02em] text-[#0f1720]">{{ $plan->name }}</h3>
                        @if ($isCurrent)
                            <span class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#3a6ea5]">Huidig plan</span>
                        @elseif ($isPending)
                            <span class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Gepland</span>
                        @elseif ($isRec)
                            <span class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#3a6ea5]">Aanbevolen</span>
                        @endif
                    </div>

                    @if ($plan->description)
                        <p class="mt-2 text-sm tracking-[-0.01em] text-[#586573]">{{ $plan->description }}</p>
                    @endif

                    <ul class="mt-5 flex-1 space-y-2.5 text-sm tracking-[-0.01em] text-[#0f1720]">
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#3a6ea5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        @if ($isCurrent)
                            <button type="button" disabled class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#e1e6ed] px-5 text-xs font-medium uppercase tracking-[0.04em] text-[#586573]">Huidig plan</button>
                        @elseif ($isPending)
                            <button type="button" disabled class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#e1e6ed] px-5 text-xs font-medium uppercase tracking-[0.04em] text-[#586573]">Wijziging gepland</button>
                        @elseif ($subscribed)
                            <button type="button" wire:click="mountAction('swap', { slug: '{{ $plan->slug }}' })" wire:loading.attr="disabled"
                                class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#002f5b] px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] disabled:opacity-50 motion-reduce:transition-none">
                                Wissel naar {{ $plan->name }}
                            </button>
                        @else
                            <button type="button" wire:click="subscribe('{{ $plan->slug }}')" wire:loading.attr="disabled"
                                class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#002f5b] px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] disabled:opacity-50 motion-reduce:transition-none">
                                Kies {{ $plan->name }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs tracking-[-0.01em] text-[#586573]">Prijzen en betaling verlopen via Stripe — je ziet het bedrag bij het afrekenen.</p>
    </div>
</x-filament-panels::page>
