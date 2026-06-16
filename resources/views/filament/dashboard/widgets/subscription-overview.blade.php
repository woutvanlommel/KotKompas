<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Abonnement</x-slot>

        <x-slot name="description">
            {{ $isSubscribed ? 'Je huidige plan' : 'Beheer je plan' }}
        </x-slot>

        <div class="space-y-4">
            @if ($isSubscribed)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Huidig plan</span>
                    <x-filament::badge color="primary">{{ $planLabel }}</x-filament::badge>
                </div>

                @if ($renewsAt)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Verlengt op</span>
                        <span class="font-medium text-gray-950">{{ $renewsAt->format('d/m/Y') }}</span>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500">
                    Je hebt nog geen actief abonnement. Kies een plan om koten uit te lichten en bovenaan de zoekresultaten te verschijnen.
                </p>
            @endif

            <x-filament::button
                tag="a"
                :href="$manageUrl"
                icon="heroicon-m-credit-card"
                class="w-full justify-center"
            >
                {{ $isSubscribed ? 'Beheer abonnement' : 'Kies een abonnement' }}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
