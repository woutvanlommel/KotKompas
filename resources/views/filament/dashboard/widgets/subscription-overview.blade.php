@php
    $pct = $slotsTotal > 0 ? min(100, (int) round($slotsUsed / $slotsTotal * 100)) : 0;
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Abonnement</x-slot>

        <x-slot name="description">
            {{ $isSubscribed ? 'Je huidige plan en uitlicht-slots' : 'Beheer je plan' }}
        </x-slot>

        <div class="space-y-4">
            @if ($isSubscribed)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Huidig plan</span>
                    <x-filament::badge color="primary">{{ $planLabel }}</x-filament::badge>
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Uitlicht-slots</span>
                        <span class="font-medium tabular-nums text-gray-950 dark:text-white">{{ $slotsUsed }} / {{ $slotsTotal }}</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-primary-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                @if ($renewsAt)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Verlengt op</span>
                        <span class="font-medium text-gray-950 dark:text-white">{{ $renewsAt->format('d/m/Y') }}</span>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">
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
