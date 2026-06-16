<x-filament-panels::page>
    @php $rooms = $this->getFavouriteRooms(); @endphp

    @if ($rooms->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 py-20 text-center dark:border-gray-700">
            <svg class="mb-4 h-10 w-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="text-base font-medium text-gray-700 dark:text-gray-300">Nog geen favorieten</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Klik het hartje op een kot om het hier op te slaan.</p>
            <a href="{{ route('rooms.index') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                Koten zoeken
                <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($rooms as $room)
                @php
                    $typeLabels = [
                        'studio'            => 'Studio',
                        'one_bedroom'       => '1 slaapkamer',
                        'two_bedroom'       => '2 slaapkamers',
                        'three_bedroom'     => '3 slaapkamers',
                        'four_bedroom'      => '4 slaapkamers',
                        'five_plus_bedroom' => '5+ slaapkamers',
                    ];
                    $type  = $typeLabels[$room->type] ?? $room->type;
                    $photo = $room->getFirstMediaUrl('cover', 'webp') ?: $room->getFirstMediaUrl('cover') ?: null;
                    $city  = $room->building?->city ?? 'Kot';
                    $price = number_format((float) $room->price_per_month, 0, ',', '.');
                @endphp

                <div class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800">

                    {{-- Cover image --}}
                    <a href="{{ route('rooms.show', $room) }}" class="block aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-700">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
                        @else
                            <div class="flex h-full items-center justify-center text-gray-300 dark:text-gray-600">
                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                                    <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        @endif
                    </a>

                    {{-- Favourite toggle — top-right of image --}}
                    <div class="absolute right-3 top-3">
                        <livewire:favourite-button :room-id="$room->id" :key="'fav-dash-' . $room->id" />
                    </div>

                    {{-- Info --}}
                    <div class="p-4">
                        <a href="{{ route('rooms.show', $room) }}" class="block">
                            <p class="truncate font-medium text-gray-900 dark:text-white">{{ $city }}</p>
                            <p class="mt-0.5 text-xs uppercase tracking-wide text-gray-400">{{ $type }}</p>
                            <p class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">
                                €{{ $price }}<span class="ml-0.5 text-xs font-normal text-gray-400">/mnd</span>
                            </p>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
