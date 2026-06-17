@php
    use App\Filament\Dashboard\Resources\Buildings\BuildingResource;
    use App\Filament\Dashboard\Resources\Rooms\RoomResource;

    $buildings = $this->getBuildings();

    // Brand status badges — rounded-md, arbitrary-hex tints (navy-editorial).
    $statusBadge = [
        'available'   => 'bg-[#e7f6ec] text-[#15803d]',
        'rented'      => 'bg-[#eaf1f8] text-[#2e5884]',
        'maintenance' => 'bg-[#fff0e6] text-[#c2510a]',
        'archived'    => 'bg-[#e1e6ed] text-[#586573]',
    ];
    $statusLabel = [
        'available'   => 'Beschikbaar',
        'rented'      => 'Verhuurd',
        'maintenance' => 'Onderhoud',
        'archived'    => 'Gearchiveerd',
    ];
@endphp

<x-filament-widgets::widget class="fi-wi relative col-span-full bg-white">
<div class="relative overflow-hidden rounded-[1.25rem]" x-data="{ expanded: [] }">

    {{-- Section marker --}}
    <div class="px-6 py-5 border-b border-[#0f17201f]">
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">003 / Portefeuille</p>
    </div>

    @if ($buildings->isEmpty())
        <div class="flex flex-col gap-5 px-6 py-10">
            <p class="text-sm tracking-[-0.01em] text-[#586573]">
                Je hebt nog geen gebouwen toegevoegd. Voeg een gebouw toe om kamers, bezetting en kotscore te beheren.
            </p>
            <a href="{{ BuildingResource::getUrl('create') }}"
               class="group inline-flex h-11 w-fit items-center gap-3 rounded-[4px] bg-[#00101e] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3a6ea5] focus-visible:ring-offset-2">
                Gebouw toevoegen
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                    <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
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
                            role="button"
                            tabindex="0"
                            :aria-expanded="expanded.includes({{ $bid }})"
                            aria-controls="building-rooms-{{ $bid }}"
                            class="cursor-pointer border-b border-[#0f17201f] transition-colors duration-[160ms] ease-[cubic-bezier(0.22,1,0.36,1)] hover:bg-[rgba(225,230,237,0.5)] focus-visible:outline-none focus-visible:bg-[rgba(225,230,237,0.5)] focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#3a6ea5] motion-reduce:transition-none"
                            @click="expanded.includes({{ $bid }}) ? expanded = expanded.filter(i => i !== {{ $bid }}) : expanded.push({{ $bid }})"
                            @keydown.enter.prevent="expanded.includes({{ $bid }}) ? expanded = expanded.filter(i => i !== {{ $bid }}) : expanded.push({{ $bid }})"
                            @keydown.space.prevent="expanded.includes({{ $bid }}) ? expanded = expanded.filter(i => i !== {{ $bid }}) : expanded.push({{ $bid }})"
                        >
                            <td class="px-6 py-4">
                                <span class="font-medium tracking-[-0.01em] text-[#0f1720]">{{ $building->name }}</span>
                                <span class="ml-2 text-xs tracking-[-0.01em] text-[#586573] sm:hidden">{{ $building->city }}</span>
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
                                    <span class="text-xs tracking-[-0.01em] text-[#586573]">Geen koten</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-medium tabular-nums text-[#0f1720] hidden lg:table-cell">
                                @if ($building->average_price !== null)
                                    € {{ number_format($building->average_price, 2, ',', '.') }}
                                @else
                                    <span class="text-[#586573]">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right hidden sm:table-cell">
                                @if ($building->score !== null && $building->reviews_count > 0)
                                    <span class="inline-flex items-baseline gap-1 tabular-nums">
                                        <span class="font-medium {{ $building->score < 3.5 ? 'text-[#c2510a]' : 'text-[#0f1720]' }}">{{ \App\Support\Score::format($building->score) }}</span>
                                        <span class="text-[#586573]">/5</span>
                                        <span class="text-[#586573]">({{ $building->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-[#586573]">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <svg
                                    class="mx-auto h-4 w-4 text-[#586573] transition-transform duration-200 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
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
                                    id="building-rooms-{{ $bid }}"
                                    x-show="expanded.includes({{ $bid }})"
                                    x-collapse
                                    x-cloak
                                >
                                    @if ($building->rooms->isEmpty())
                                        <p class="px-10 py-4 text-sm tracking-[-0.01em] text-[#586573]">
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
                                                                <a href="{{ $roomUrl }}"
                                                                   class="font-medium tracking-[-0.01em] text-[#0f1720] rounded-[2px] hover:text-[#00101e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3a6ea5] focus-visible:ring-offset-2">
                                                                    {{ $room->title ?? ('Kamer ' . $room->room_number) }}
                                                                </a>
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
                                                                <span class="text-[#586573]">—</span>
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
</x-filament-widgets::widget>
