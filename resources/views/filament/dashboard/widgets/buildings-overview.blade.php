@php
    use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
    use App\Filament\Dashboard\Resources\Rooms\RoomResource;

    $buildings = $this->getBuildings();

    // Brand status badges — rounded-md, arbitrary-hex tints (navy-editorial).
    $statusBadge = [
        'available' => 'bg-[#e7f6ec] text-[#15803d]',
        'rented'    => 'bg-[#eaf1f8] text-[#2e5884]',
        'archived'  => 'bg-[#e1e6ed] text-[#586573]',
    ];
    $statusLabel = [
        'available' => 'Beschikbaar',
        'rented'    => 'Verhuurd',
        'archived'  => 'Gearchiveerd',
    ];
@endphp

<div {{ $attributes->class(['fi-wi relative col-span-full overflow-hidden bg-white']) }}
     x-data="{ expanded: [] }">

    {{-- Header --}}
    <div class="px-6 py-5 border-b border-[#0f17201f]">
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Portefeuille</p>
        <h3 class="mt-1.5 text-base font-medium tracking-[-0.01em] text-[#0f1720]">Gebouwen overzicht</h3>
        <p class="mt-1 text-sm tracking-[-0.01em] text-[#586573]">Bezetting, huurprijs en kotscore per kamer.</p>
    </div>

    @if ($buildings->isEmpty())
        <div class="px-6 py-12 text-center text-sm tracking-[-0.01em] text-[#9aa6b4]">
            Geen gebouwen gevonden.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-[#e1e6ed]">
                        <th class="px-6 py-2.5 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Gebouw</th>
                        <th class="px-4 py-2.5 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] hidden sm:table-cell">Stad</th>
                        <th class="px-4 py-2.5 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] hidden md:table-cell">Bezetting</th>
                        <th class="px-4 py-2.5 text-right text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] tabular-nums hidden lg:table-cell">Gem. huurprijs</th>
                        <th class="px-4 py-2.5 text-right text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] tabular-nums hidden sm:table-cell">Kotscore</th>
                        <th class="px-4 py-2.5 w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($buildings as $building)
                        @php
                            $bid         = $building->id;
                            $buildingUrl = BuildingResource::getUrl('view', ['record' => $building]);
                        @endphp

                        {{-- Building row --}}
                        <tr
                            class="cursor-pointer border-b border-[#0f17201f] transition-colors duration-[160ms] ease-[cubic-bezier(0.22,1,0.36,1)] hover:bg-[rgba(225,230,237,0.5)] motion-reduce:transition-none"
                            @click="expanded.includes({{ $bid }}) ? expanded = expanded.filter(i => i !== {{ $bid }}) : expanded.push({{ $bid }})"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium tracking-[-0.01em] text-[#0f1720]">{{ $building->name }}</span>
                                <span class="ml-2 text-xs tracking-[-0.01em] text-[#9aa6b4] sm:hidden">{{ $building->city }}</span>
                            </td>
                            <td class="px-4 py-4 tracking-[-0.01em] text-[#586573] hidden sm:table-cell">
                                {{ $building->city }}
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                @if ($building->rooms_count > 0)
                                    <span class="inline-flex items-center rounded-[0.375rem] px-2 py-0.5 text-xs font-medium tabular-nums {{ $building->available_rooms_count > 0 ? 'bg-[#e7f6ec] text-[#15803d]' : 'bg-[#eaf1f8] text-[#2e5884]' }}">
                                        {{ $building->rented_rooms_count }} / {{ $building->rooms_count }}
                                    </span>
                                @else
                                    <span class="text-xs tracking-[-0.01em] text-[#9aa6b4]">Geen koten</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-medium tabular-nums text-[#0f1720] hidden lg:table-cell">
                                @if ($building->average_price !== null)
                                    € {{ number_format($building->average_price, 2, ',', '.') }}
                                @else
                                    <span class="text-[#9aa6b4]">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right hidden sm:table-cell">
                                @if ($building->score !== null && $building->reviews_count > 0)
                                    <span class="inline-flex items-baseline gap-1 tabular-nums">
                                        <span class="text-[#3a6ea5]" aria-hidden="true">★</span>
                                        <span class="font-medium {{ $building->score < 3.5 ? 'text-[#c2510a]' : 'text-[#0f1720]' }}">{{ \App\Support\Score::format($building->score) }}</span>
                                        <span class="text-[#9aa6b4]">/5</span>
                                        <span class="text-[#9aa6b4]">({{ $building->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-[#9aa6b4]">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <svg
                                    class="mx-auto h-4 w-4 text-[#9aa6b4] transition-transform duration-200 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                    :class="expanded.includes({{ $bid }}) ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                        </tr>

                        {{-- Expandable room section --}}
                        <tr class="border-b border-[#0f17201f]">
                            <td colspan="6" class="p-0">
                                <div
                                    x-show="expanded.includes({{ $bid }})"
                                    x-collapse
                                    x-cloak
                                >
                                    @if ($building->rooms->isEmpty())
                                        <p class="px-10 py-4 text-sm tracking-[-0.01em] text-[#9aa6b4]">
                                            Geen koten gevonden.
                                        </p>
                                    @else
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-[#e1e6ed]">
                                                    <th class="pl-10 pr-4 py-2 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Kamer</th>
                                                    <th class="px-4 py-2 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] hidden sm:table-cell">Status</th>
                                                    <th class="px-4 py-2 text-left text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] hidden md:table-cell">Huurder</th>
                                                    <th class="px-4 py-2 text-right text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-[#586573] tabular-nums hidden lg:table-cell">Huurprijs/mnd</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($building->rooms as $room)
                                                    @php
                                                        $roomUrl = RoomResource::getUrl('view', ['record' => $room]);
                                                        $badge   = $statusBadge[$room->status] ?? $statusBadge['archived'];
                                                        $label   = $statusLabel[$room->status] ?? $room->status;
                                                    @endphp
                                                    <tr
                                                        onclick="window.location.href='{{ $roomUrl }}'"
                                                        class="cursor-pointer border-b border-[#0f17201f] transition-colors duration-[160ms] ease-[cubic-bezier(0.22,1,0.36,1)] hover:bg-[rgba(225,230,237,0.5)] motion-reduce:transition-none"
                                                    >
                                                        <td class="pl-10 pr-4 py-3">
                                                            <div class="flex items-center gap-2.5">
                                                                <span class="h-1 w-1 shrink-0 rounded-full bg-[#b8c2cf]"></span>
                                                                <span class="font-medium tracking-[-0.01em] text-[#0f1720]">
                                                                    {{ $room->title ?? ('Kamer ' . $room->room_number) }}
                                                                </span>
                                                                <span class="inline-flex items-center rounded-[0.375rem] px-2 py-0.5 text-xs font-medium {{ $badge }} sm:hidden">{{ $label }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 hidden sm:table-cell">
                                                            <span class="inline-flex items-center rounded-[0.375rem] px-2 py-0.5 text-xs font-medium {{ $badge }}">
                                                                {{ $label }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 tracking-[-0.01em] text-[#586573] hidden md:table-cell">
                                                            {{ $room->tenant?->name ?? '—' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-right font-medium tabular-nums text-[#0f1720] hidden lg:table-cell">
                                                            @if ($room->price_per_month !== null)
                                                                € {{ number_format($room->price_per_month, 2, ',', '.') }}
                                                            @else
                                                                <span class="text-[#9aa6b4]">—</span>
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
