@php
    use App\Filament\Dashboard\Resources\Rooms\RoomResource;

    $building = $getRecord();
    $buildingId = $building->id;
    $rooms = $building->rooms;

    $statusLabels = [
        'available' => 'Beschikbaar',
        'rented'    => 'Verhuurd',
        'archived'  => 'Gearchiveerd',
    ];
    $statusBadgeClasses = [
        'available' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
        'rented'    => 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400',
        'archived'  => 'bg-gray-100 text-gray-500 dark:bg-gray-500/20 dark:text-gray-400',
    ];
    $statusBorderClasses = [
        'available' => 'border-l-success-400',
        'rented'    => 'border-l-primary-400',
        'archived'  => 'border-l-gray-300 dark:border-l-gray-600',
    ];
@endphp

<div
    x-show="$wire.expandedBuildings.includes({{ $buildingId }})"
    x-collapse
    x-cloak
    class="py-3"
>
    @if ($rooms->isEmpty())
        <div class="px-6 py-5 text-sm text-gray-400 dark:text-gray-500 italic">
            Geen koten gevonden voor dit gebouw.
        </div>
    @else

        {{-- Table — zichtbaar vanaf xl --}}
        <div class="hidden xl:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-y border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider w-1/3">Kamer</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Huurder</th>
                        <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Huurprijs/mnd</th>
                        <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($rooms as $room)
                        @php
                            $statusLabel   = $statusLabels[$room->status] ?? $room->status;
                            $badgeClass    = $statusBadgeClasses[$room->status] ?? $statusBadgeClasses['archived'];
                            $roomUrl       = RoomResource::getUrl('view', ['record' => $room]);
                        @endphp
                        <tr
                            class="group hover:bg-gray-50 dark:hover:bg-white/[0.03] cursor-pointer transition-colors duration-100"
                            onclick="window.location.href='{{ $roomUrl }}'"
                        >
                            <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                                {{ $room->title ?? ('Kamer ' . $room->room_number) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                                {{ $room->tenant?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900 dark:text-white tabular-nums">
                                {{ $room->price_per_month !== null
                                    ? '€' . number_format($room->price_per_month, 2, ',', '.')
                                    : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($room->score !== null)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $room->score >= 4.0 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-500/20 dark:text-gray-400' }}">
                                        ★ {{ number_format($room->score, 1, ',', '.') }}
                                        <span class="opacity-60">({{ $room->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cards — zichtbaar onder xl --}}
        <div class="xl:hidden grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
            @foreach ($rooms as $room)
                @php
                    $statusLabel  = $statusLabels[$room->status] ?? $room->status;
                    $badgeClass   = $statusBadgeClasses[$room->status] ?? $statusBadgeClasses['archived'];
                    $borderClass  = $statusBorderClasses[$room->status] ?? $statusBorderClasses['archived'];
                    $roomUrl      = RoomResource::getUrl('view', ['record' => $room]);
                @endphp
                <a
                    href="{{ $roomUrl }}"
                    class="flex flex-col gap-3 border-l-4 {{ $borderClass }} bg-white dark:bg-white/[0.03] border border-gray-100 dark:border-white/10 rounded-r-lg px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-white/[0.06] transition-colors duration-150 shadow-sm"
                >
                    {{-- Header: naam + status --}}
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-snug">
                            {{ $room->title ?? ('Kamer ' . $room->room_number) }}
                        </span>
                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Huurder --}}
                    @if ($room->tenant)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Huurder</span>
                            <span class="text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $room->tenant->name }}</span>
                        </div>
                    @else
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Huurder</span>
                            <span class="text-sm text-gray-400 dark:text-gray-600 italic">Geen huurder</span>
                        </div>
                    @endif

                    {{-- Footer: prijs + score --}}
                    <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-white/10">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums">
                            {{ $room->price_per_month !== null
                                ? '€ ' . number_format($room->price_per_month, 2, ',', '.')
                                : '—' }}
                            <span class="text-xs font-normal text-gray-400">/mnd</span>
                        </span>

                        @if ($room->score !== null)
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $room->score >= 4.0 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-500/20 dark:text-gray-400' }}">
                                ★ {{ number_format($room->score, 1, ',', '.') }}
                                <span class="opacity-60">({{ $room->reviews_count }})</span>
                            </span>
                        @else
                            <span class="text-xs text-gray-300 dark:text-gray-600">Geen score</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

    @endif
</div>
