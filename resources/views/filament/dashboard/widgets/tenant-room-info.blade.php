@php
    $typeLabels = [
        'studio' => 'Studio',
        'one_bedroom' => '1 slaapkamer',
        'two_bedroom' => '2 slaapkamers',
        'three_bedroom' => '3 slaapkamers',
        'four_bedroom' => '4 slaapkamers',
        'five_plus_bedroom' => '5+ slaapkamers',
    ];
@endphp

<x-filament-widgets::widget>
    @if ($rooms->isEmpty())
        <x-filament::section>
            <x-filament::empty-state
                icon="heroicon-o-home"
                icon-color="gray"
                heading="Nog geen kot gekoppeld"
                description="Zodra je verhuurder je aan een kot koppelt, zie je hier alle info over je kot en gebouw."
                :contained="false"
                compact
            />
        </x-filament::section>
    @else
        <div class="grid gap-6">
            @foreach ($rooms as $room)
                <x-filament::section>
                    <div class="flex flex-col gap-6 sm:flex-row">
                        @if ($cover = $room->getFirstMediaUrl('cover', 'thumb'))
                            <img
                                src="{{ $cover }}"
                                alt="{{ $room->title }}"
                                class="h-40 w-full rounded-lg object-cover sm:w-56"
                            >
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="fi-section-header-heading">{{ $room->title }}</h2>
                                <x-filament::badge :color="$room->status === 'rented' ? 'info' : 'gray'">
                                    {{ $room->status === 'rented' ? 'Verhuurd aan jou' : ucfirst($room->status) }}
                                </x-filament::badge>
                            </div>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $room->building->name }} — {{ $room->full_address }}
                            </p>

                            <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                                <div>
                                    <dt class="text-gray-500">Type</dt>
                                    <dd class="font-medium">{{ $typeLabels[$room->type] ?? $room->type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Oppervlakte</dt>
                                    <dd class="font-medium">{{ $room->surface_m2 ? $room->surface_m2 . ' m²' : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Gemeubeld</dt>
                                    <dd class="font-medium">{{ $room->is_furnished ? 'Ja' : 'Nee' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Basishuur</dt>
                                    <dd class="font-medium">€ {{ number_format((float) $room->price_per_month, 2, ',', '.') }} /maand</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Totaal per maand</dt>
                                    <dd class="font-medium">€ {{ number_format($room->total_monthly_price, 2, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Waarborg</dt>
                                    <dd class="font-medium">{{ $room->deposit_amount ? '€ ' . number_format((float) $room->deposit_amount, 2, ',', '.') : '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>
