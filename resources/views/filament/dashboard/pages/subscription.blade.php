<x-filament-panels::page>
    @php($plans = $this->getPlans())
    @php($subscribed = $this->isSubscribed())
    @php($subscription = $this->currentSubscription())
    @php($currentPriceId = $this->currentPriceId())

    @if ($subscribed && $subscription)
        <x-filament::section>
            <x-slot name="heading">Je huidige abonnement</x-slot>

            <div class="flex items-center justify-between gap-4">
                <div class="text-sm">
                    @if ($subscription->onGracePeriod())
                        <p class="text-warning-600 font-medium">
                            Opgezegd — loopt af op {{ $subscription->ends_at?->translatedFormat('d F Y') }}.
                        </p>
                    @else
                        <p class="text-gray-600">Je abonnement is actief.</p>
                    @endif
                </div>

                <div class="flex gap-2">
                    @if ($subscription->onGracePeriod())
                        <x-filament::button wire:click="resume" color="success">Hervatten</x-filament::button>
                    @else
                        <x-filament::button wire:click="cancel" color="danger" outlined>Opzeggen</x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::section>
    @endif

    <div class="grid gap-6 md:grid-cols-3">
        @foreach ($plans as $plan)
            @php($isCurrent = $currentPriceId !== null && $currentPriceId === $plan->priceId())

            <div @class([
                'flex flex-col rounded-xl border bg-white p-6 dark:bg-gray-900',
                'ring-2 ring-primary-500' => $isCurrent,
            ])>
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
                    @elseif ($subscribed)
                        <x-filament::button class="w-full" color="gray"
                            wire:click="swap('{{ $plan->slug }}')">
                            Wissel naar {{ $plan->name }}
                        </x-filament::button>
                    @else
                        <x-filament::button class="w-full"
                            wire:click="subscribe('{{ $plan->slug }}')">
                            Kies {{ $plan->name }}
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>