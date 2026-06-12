@php
    use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
    use App\Filament\Dashboard\Resources\Rooms\RoomResource;

    $buildings = $this->getBuildings();

    $statusBadge = [
        'available' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
        'rented'    => 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400',
        'archived'  => 'bg-gray-100 text-gray-500 dark:bg-gray-500/20 dark:text-gray-400',
    ];
    $statusLabel = [
        'available' => 'Beschikbaar',
        'rented'    => 'Verhuurd',
        'archived'  => 'Gearchiveerd',
    ];
@endphp

<div {{ $attributes->class(['fi-wi relative col-span-full rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10']) }}
     x-data="{ expanded: [] }">

    {{-- Header --}}
    <div class="flex items-center px-6 py-4 border-b border-gray-100 dark:border-white/10">
        <div>
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Gebouwen overzicht</h3>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Bezetting, huurprijs en kotscore per kamer</p>
        </div>
    </div>

    @if ($buildings->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
            Geen gebouwen gevonden.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-white/[0.02]">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-white/10">Gebouw</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-white/10 hidden sm:table-cell">Stad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-white/10 hidden md:table-cell">Bezetting</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-white/10 hidden lg:table-cell">Gem. huurprijs</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-white/10 hidden sm:table-cell">Kotscore</th>
                        <th class="px-4 py-3 border-b border-gray-100 dark:border-white/10 w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($buildings as $building)
                        @php
                            $bid     = $building->id;
                            $buildingUrl = BuildingResource::getUrl('view', ['record' => $building]);
                        @endphp

                        {{-- Building row --}}
                        <tr
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/[0.025] transition-colors duration-100 border-b border-gray-100 dark:border-white/[0.06]"
                            @click="expanded.includes({{ $bid }}) ? expanded = expanded.filter(i => i !== {{ $bid }}) : expanded.push({{ $bid }})"
                        >
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $building->name }}</span>
                                <span class="sm:hidden ml-2 text-xs text-gray-400">{{ $building->city }}</span>
                            </td>
                            <td class="px-4 py-4 text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                {{ $building->city }}
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                @if ($building->rooms_count > 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $building->available_rooms_count > 0 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-500/20 dark:text-gray-400' }}">
                                        {{ $building->rented_rooms_count }} / {{ $building->rooms_count }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-600">Geen koten</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-white tabular-nums hidden lg:table-cell">
                                @if ($building->average_price !== null)
                                    € {{ number_format($building->average_price, 2, ',', '.') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right hidden sm:table-cell">
                                @if ($building->score !== null && $building->reviews_count > 0)
                                    <span class="inline-flex items-center gap-1 font-medium text-gray-900 dark:text-white">
                                        <span class="text-amber-500">★</span>
                                        <span>{{ number_format($building->score, 1, ',', '.') }} ({{ $building->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <svg
                                    class="w-4 h-4 text-gray-400 dark:text-gray-500 mx-auto transition-transform duration-200"
                                    :class="expanded.includes({{ $bid }}) ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                        </tr>

                        {{-- Expandable room section --}}
                        <tr class="border-b border-gray-100 dark:border-white/[0.06]">
                            <td colspan="6" class="p-0">
                                <div
                                    x-show="expanded.includes({{ $bid }})"
                                    x-collapse
                                    x-cloak
                                >
                                    @if ($building->rooms->isEmpty())
                                        <p class="px-10 py-3 text-sm text-gray-400 dark:text-gray-500 italic">
                                            Geen koten gevonden.
                                        </p>
                                    @else
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50/60 dark:bg-white/[0.015]">
                                                    <th class="pl-10 pr-4 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-wider">Kamer</th>
                                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-wider hidden sm:table-cell">Status</th>
                                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-wider hidden md:table-cell">Huurder</th>
                                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-wider hidden lg:table-cell">Huurprijs/mnd</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100/80 dark:divide-white/[0.04]">
                                                @foreach ($building->rooms as $room)
                                                    @php
                                                        $roomUrl = RoomResource::getUrl('view', ['record' => $room]);
                                                        $badge   = $statusBadge[$room->status] ?? $statusBadge['archived'];
                                                        $label   = $statusLabel[$room->status] ?? $room->status;
                                                    @endphp
                                                    <tr
                                                        onclick="window.location.href='{{ $roomUrl }}'"
                                                        class="cursor-pointer hover:bg-gray-100/60 dark:hover:bg-white/[0.03] transition-colors duration-100"
                                                    >
                                                        <td class="pl-10 pr-4 py-3">
                                                            <div class="flex items-center gap-2.5">
                                                                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600 shrink-0"></span>
                                                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ $room->title ?? ('Kamer ' . $room->room_number) }}
                                                                </span>
                                                                <span class="sm:hidden inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 hidden sm:table-cell">
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                                                {{ $label }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                            {{ $room->tenant?->name ?? '—' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300 tabular-nums hidden lg:table-cell">
                                                            @if ($room->price_per_month !== null)
                                                                € {{ number_format($room->price_per_month, 2, ',', '.') }}
                                                            @else
                                                                <span class="text-gray-400">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
