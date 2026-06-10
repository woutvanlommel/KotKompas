<x-filament-widgets::widget>
    <x-filament::section heading="Jouw verhuurder">
        <div class="grid gap-6">
            @foreach ($landlords as $entry)
                @php
                    $landlord = $entry['landlord'];
                @endphp
                <div class="flex items-center gap-4">
                    <x-filament::avatar
                        :src="\Filament\Facades\Filament::getUserAvatarUrl($landlord)"
                        :alt="$landlord->full_name"
                        size="lg"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="font-medium">{{ $landlord->full_name }}</p>
                        <p class="text-sm text-gray-500">
                            Verhuurder van {{ $entry['rooms']->pluck('title')->join(', ') }}
                        </p>
                    </div>
                </div>

                <dl class="grid gap-2 text-sm">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 text-gray-400" />
                        <a href="mailto:{{ $landlord->email }}" class="text-primary-600 hover:underline">{{ $landlord->email }}</a>
                    </div>
                    @if ($landlord->phone)
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-phone" class="h-4 w-4 text-gray-400" />
                            <a href="tel:{{ $landlord->phone }}" class="text-primary-600 hover:underline">{{ $landlord->phone }}</a>
                        </div>
                    @endif
                </dl>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
