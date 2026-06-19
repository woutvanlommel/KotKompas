<x-filament-panels::page>
    @php $rooms = $this->getFavouriteRooms(); @endphp

    @if ($rooms->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-[1.25rem] border border-dashed border-[#0f17201f] py-20 text-center">
            <svg class="mb-4 h-10 w-10 text-[#9aa6b4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="text-base font-medium tracking-[-0.01em] text-[#0f1720]">Nog geen favorieten</p>
            <p class="mt-1 text-sm tracking-[-0.01em] text-[#586573]">Klik het hartje op een kot om het hier op te slaan.</p>
            <a href="{{ route('rooms.index') }}"
               class="group mt-6 inline-flex items-center gap-2.5 rounded-[4px] bg-[#0f1720] py-2.5 pl-4 pr-3 text-sm font-medium text-white transition-colors duration-300 hover:bg-[#00101e]">
                Koten zoeken
                <svg class="h-3.5 w-3.5 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($rooms as $room)
                @php
                    $typeLabels = [
                        'kamer'       => 'Kamer',
                        'studio'      => 'Studio',
                        'appartement' => 'Appartement',
                    ];
                    $type  = $typeLabels[$room->type] ?? $room->type;
                    $photo = $room->getFirstMediaUrl('cover', 'webp') ?: $room->getFirstMediaUrl('cover') ?: null;
                    $city  = $room->building?->city ?? 'Kot';
                    $price = number_format((float) $room->price_per_month, 0, ',', '.');
                @endphp

                <div class="group relative overflow-hidden rounded-[1.25rem] border border-[#0f17201f] bg-white transition-shadow duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:shadow-[0_18px_40px_-24px_rgba(0,47,91,0.45)] motion-reduce:transition-none">

                    {{-- Cover image --}}
                    <a href="{{ route('rooms.show', $room) }}" class="block aspect-[4/3] overflow-hidden bg-[#e1e6ed]">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
                        @else
                            <div class="flex h-full items-center justify-center text-[#9aa6b4]">
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
                            <p class="truncate font-medium tracking-[-0.01em] text-[#0f1720]">{{ $city }}</p>
                            <p class="mt-0.5 text-xs uppercase tracking-[0.12em] text-[#9aa6b4]">{{ $type }}</p>
                            <p class="mt-3 text-lg font-medium tracking-[-0.01em] tabular-nums text-[#0f1720]">
                                €{{ $price }}<span class="ml-0.5 text-xs font-normal text-[#9aa6b4]">/mnd</span>
                            </p>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
