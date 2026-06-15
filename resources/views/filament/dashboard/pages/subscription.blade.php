<x-filament-panels::page>
    @php
        $plans = $this->getPlans();
        $subscribed = $this->isSubscribed();
        $subscription = $this->currentSubscription();
        $currentPriceId = $this->currentPriceId();
        $pendingPlan = $this->pendingPlan();
        $renewal = $this->getRenewalDate()?->locale('nl');
    @endphp

    {{-- Huidig abonnement --}}
    @if ($subscribed && $subscription)
        <x-filament::section>
            <x-slot name="heading">Je huidige abonnement</x-slot>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1 text-sm">
                    @if ($subscription->onGracePeriod())
                        <p class="font-medium text-warning-600">
                            Opgezegd — verloopt op {{ $renewal?->isoFormat('D MMMM YYYY') }}
                            @if ($renewal)
                                <span class="text-gray-500">({{ $renewal->diffForHumans() }})</span>
                            @endif
                        </p>
                        <p class="text-gray-500">Je behoudt toegang tot die datum.</p>
                    @else
                        <p class="font-medium text-gray-950 dark:text-white">Je abonnement is actief.</p>
                        @if ($renewal)
                            <p class="text-gray-500">
                                Verlengt op {{ $renewal->isoFormat('D MMMM YYYY') }} ({{ $renewal->diffForHumans() }}).
                            </p>
                        @endif
                    @endif
                </div>

                <div class="flex shrink-0 gap-2">
                    @if ($subscription->onGracePeriod())
                        <x-filament::button color="success" wire:click="mountAction('resume')">
                            Hervatten
                        </x-filament::button>
                    @else
                        <x-filament::button color="danger" outlined wire:click="mountAction('cancel')">
                            Opzeggen
                        </x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Geplande wijziging --}}
            @if ($pendingPlan)
                <div class="mt-4 flex flex-col gap-3 rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-900 dark:bg-primary-950/40 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3 text-sm">
                        <x-filament::icon icon="heroicon-o-clock" class="mt-0.5 h-5 w-5 shrink-0 text-primary-600" />
                        <span class="text-primary-900 dark:text-primary-100">
                            Wijziging gepland naar <strong>{{ $pendingPlan->name }}</strong>@if ($renewal) — gaat in op {{ $renewal->isoFormat('D MMMM YYYY') }} ({{ $renewal->diffForHumans() }})@endif
                        </span>
                    </div>
                    <x-filament::button size="sm" color="gray" wire:click="mountAction('cancelPendingSwap')">
                        Wijziging annuleren
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>
    @endif

    {{-- Plannen --}}
    <div class="grid gap-6 md:grid-cols-3">
        @foreach ($plans as $plan)
            @php
                $isCurrent = $currentPriceId !== null && $currentPriceId === $plan->priceId();
                $isPending = $pendingPlan && $pendingPlan->id === $plan->id;
            @endphp

            <div @class([
                'relative flex flex-col rounded-2xl border bg-white p-6 shadow-sm transition dark:bg-gray-900',
                'border-primary-500 ring-2 ring-primary-500' => $isCurrent,
                'border-gray-200 hover:shadow-md dark:border-white/10' => ! $isCurrent,
            ])>
                @if ($isCurrent)
                    <span class="absolute -top-3 left-6 rounded-full bg-primary-500 px-3 py-0.5 text-xs font-semibold text-white">
                        Huidig plan
                    </span>
                @elseif ($isPending)
                    <span class="absolute -top-3 left-6 rounded-full bg-primary-100 px-3 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-primary-300">
                        Gepland
                    </span>
                @endif

                <h3 class="text-lg font-bold text-gray-950 dark:text-white">{{ $plan->name }}</h3>

                @if ($plan->description)
                    <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>
                @endif

                <ul class="mt-4 flex-1 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    @foreach (($plan->features ?? []) as $feature)
                        <li class="flex items-start gap-2">
                            <x-filament::icon icon="heroicon-o-check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-primary-500" />
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-6">
                    @if ($isCurrent)
                        <x-filament::button class="w-full" disabled>Huidig plan</x-filament::button>
                    @elseif ($isPending)
                        <x-filament::button class="w-full" color="gray" disabled>Wijziging gepland</x-filament::button>
                    @elseif ($subscribed)
                        <x-filament::button class="w-full" color="gray"
                            wire:click="mountAction('swap', { slug: '{{ $plan->slug }}' })">
                            Wissel naar {{ $plan->name }}
                        </x-filament::button>
                    @else
                        <x-filament::button class="w-full" wire:click="subscribe('{{ $plan->slug }}')">
                            Kies {{ $plan->name }}
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
