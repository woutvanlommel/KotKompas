@php
    use App\Filament\Dashboard\Resources\Buildings\BuildingResource;

    $buildings = $this->getBuildings();
@endphp

<x-filament-widgets::widget class="fi-wi relative col-span-full bg-white">
<div class="relative overflow-hidden rounded-[1.25rem]">

    {{-- Section marker --}}
    <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-[#0f17201f]">
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">003 / Portefeuille</p>
        @unless ($buildings->isEmpty())
            <a href="{{ BuildingResource::getUrl() }}" class="kk-card-link">
                Bekijk alles
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M7 17 17 7M17 7H9M17 7v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @endunless
    </div>

    @if ($buildings->isEmpty())
        <div class="flex flex-col gap-5 px-6 py-10">
            <p class="text-sm tracking-[-0.01em] text-[#586573]">
                Je hebt nog geen gebouwen toegevoegd. Voeg een gebouw toe om kamers, bezetting en kotscore te beheren.
            </p>
            <a href="{{ BuildingResource::getUrl('create') }}"
               class="group inline-flex h-11 w-fit items-center gap-3 rounded-[4px] bg-[#002f5b] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3a6ea5] focus-visible:ring-offset-2">
                Gebouw toevoegen
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                    <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
        </div>
    @else
        <div class="grid gap-5 p-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($buildings as $building)
                @php
                    $buildingUrl = BuildingResource::getUrl('view', ['record' => $building]);
                    $hasReviews  = $building->score !== null && $building->reviews_count > 0;
                @endphp

                <div class="group relative flex flex-col rounded-[1rem] border border-[#0f17201f] bg-white p-5 transition-shadow duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:shadow-[0_18px_40px_-24px_rgba(0,47,91,0.45)] focus-within:shadow-[0_18px_40px_-24px_rgba(0,47,91,0.45)] motion-reduce:transition-none">

                    {{-- Head --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-medium tracking-[-0.01em] text-[#0f1720]">
                                <a href="{{ $buildingUrl }}"
                                   class="rounded-[2px] before:absolute before:inset-0 before:content-[''] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3a6ea5] focus-visible:ring-offset-2">
                                    {{ $building->name }}
                                </a>
                            </h3>
                            <p class="mt-0.5 truncate text-xs tracking-[-0.01em] text-[#586573]">{{ $building->city }}</p>
                        </div>
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#9aa6b4] transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    {{-- Body: dominant occupancy figure --}}
                    <div class="mt-5 flex flex-col gap-1.5">
                        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Bezetting</p>
                        @if ($building->rooms_count > 0)
                            <p class="flex items-baseline gap-1.5 text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">
                                {{ $building->rented_rooms_count }}<span class="text-[#9aa6b4]">/</span>{{ $building->rooms_count }}
                                <span class="ml-1 inline-flex items-center rounded-[0.375rem] px-2 py-0.5 text-xs font-medium tabular-nums {{ $building->available_rooms_count > 0 ? 'bg-[#e7f6ec] text-[#15803d]' : 'bg-[#eaf1f8] text-[#2e5884]' }}">
                                    {{ $building->available_rooms_count > 0 ? $building->available_rooms_count . ' vrij' : 'Vol' }}
                                </span>
                            </p>
                        @else
                            <p class="text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] text-[#586573]">Geen koten</p>
                        @endif
                    </div>

                    {{-- Body: supporting metrics --}}
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        {{-- Kotscore --}}
                        <div class="flex flex-col gap-1">
                            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Kotscore</p>
                            @if ($hasReviews)
                                <span class="inline-flex items-baseline gap-1 text-xs tabular-nums">
                                    <span class="text-[#3a6ea5]" aria-hidden="true">&starf;</span>
                                    <span class="font-medium {{ $building->score < 3.5 ? 'border-b border-[#c2510a]/30 text-[#c2510a]' : 'text-[#0f1720]' }}">{{ \App\Support\Score::format($building->score) }}</span>
                                    <span class="text-[0.6875rem] text-[#586573]">({{ $building->reviews_count }})</span>
                                </span>
                            @else
                                <span class="text-xs text-[#586573]">&mdash;</span>
                            @endif
                        </div>

                        {{-- Gem. huurprijs --}}
                        <div class="flex flex-col gap-1">
                            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Gem. huurprijs</p>
                            @if ($building->average_price !== null)
                                <span class="text-xs font-medium tabular-nums text-[#0f1720]">&euro; {{ number_format($building->average_price, 2, ',', '.') }}</span>
                            @else
                                <span class="text-xs text-[#586573]">&mdash;</span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-5 flex items-center gap-3 border-t border-[#0f17201f] pt-4">
                        <p class="truncate text-xs tracking-[-0.01em] text-[#586573]">
                            {{ $building->rooms_count }} {{ $building->rooms_count === 1 ? 'kamer' : 'kamers' }} &middot; {{ $building->available_rooms_count }} beschikbaar
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-filament-widgets::widget>
