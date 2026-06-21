{{-- Partial: kamerlijst op basis van kaartgrenzen.
     Geladen via fetch() vanuit de rooms-map component. Geen layout wrapper.
     Respecteert $filters['view'] zodat grid/lijst/kaart-weergave klopt. --}}

@if ($rooms->isNotEmpty())
    <p class="mb-4 text-sm text-ink/55">
        {{ $rooms->total() }} {{ $rooms->total() === 1 ? 'kot' : 'koten' }} in dit gebied
    </p>

    @if ($filters['view'] === 'list')
        <div>
            @foreach ($rooms as $room)
                <x-koten-row :room="$room" />
            @endforeach
        </div>
    @elseif ($filters['view'] === 'map')
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            @foreach ($rooms as $room)
                <x-koten-card :room="$room" />
            @endforeach
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($rooms as $room)
                <x-koten-card :room="$room" />
            @endforeach
        </div>
    @endif

    <div class="mt-10">
        {{ $rooms->links('components.kk-pagination') }}
    </div>
@else
    <div class="rounded-2xl border border-dashed border-ink/15 py-20 text-center">
        <p class="text-lg font-medium">Geen koten in dit gebied</p>
        <p class="mt-2 text-sm text-ink/55">Verplaats of vergroot de kaart om meer koten te zien.</p>
    </div>
@endif
