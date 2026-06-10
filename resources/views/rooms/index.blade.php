<x-layout title="Koten zoeken — KotKompas" body-class="bg-canvas text-ink">

    <x-public-nav />

    <section class="mx-auto w-full max-w-352 px-5 pb-24 pt-32 sm:px-8">

        <header class="mb-10">
            <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
                <span class="inline-block h-px w-9 bg-accent-500"></span> Aanbod
            </p>
            <h1 class="text-[clamp(2.2rem,5vw,4rem)] font-medium leading-[0.9] tracking-[-0.04em]">
                Koten zoeken
            </h1>
        </header>

        @php
            $activeFilterCount = count(array_filter([
                $filters['q'], $filters['type'], $filters['price_min'],
                $filters['price_max'], $filters['surface_min'], $filters['furnished'],
            ]));
        @endphp

        {{-- Mobiel: filters inklapbaar zodat de resultaten meteen zichtbaar zijn --}}
        <button type="button" data-filters-toggle aria-controls="kk-filters"
                aria-expanded="{{ $activeFilterCount ? 'true' : 'false' }}"
                class="mb-6 flex w-full items-center justify-between gap-3 border-y border-hairline py-3.5 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink lg:hidden">
            <span class="flex items-center gap-2.5">
                Filters
                @if ($activeFilterCount)
                    <span class="grid h-5 min-w-5 place-items-center rounded-full bg-ink px-1.5 text-[0.625rem] leading-none text-white">{{ $activeFilterCount }}</span>
                @endif
            </span>
            <svg class="kk-filters-chevron h-3.5 w-3.5 text-ink-soft" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        {{-- Filterbalk — GET zodat filters in de URL leven (deelbaar, paginatie-vriendelijk).
             Underline-velden: de auth/login-taal op het lichte canvas. --}}
        <form method="GET" action="{{ route('rooms.index') }}" id="kk-filters" data-filters-panel
              @unless ($activeFilterCount) data-collapsed @endunless
              class="kk-filters mb-12 grid grid-cols-1 gap-x-10 gap-y-7 border-b border-hairline pb-10 sm:grid-cols-2 lg:grid-cols-4 lg:border-y lg:py-9">

            <label class="lg:col-span-2" data-suggest-anchor>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Waar zoek je?</span>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Stad, postcode of trefwoord…"
                       data-suggest data-suggest-url="{{ route('rooms.suggestions') }}"
                       class="kk-field">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Type</span>
                <select name="type" class="kk-field">
                    <option value="">Alle types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>
                            {{ \Illuminate\Support\Str::of($type)->replace('_', ' ')->ucfirst() }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Sorteer</span>
                <select name="sort" class="kk-field">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Nieuwste eerst</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Prijs laag → hoog</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Prijs hoog → laag</option>
                    <option value="surface_desc" @selected($filters['sort'] === 'surface_desc')>Grootste eerst</option>
                </select>
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Min. prijs (€)</span>
                <input type="number" name="price_min" min="0" step="50" value="{{ $filters['price_min'] }}" class="kk-field">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Max. prijs (€)</span>
                <input type="number" name="price_max" min="0" step="50" value="{{ $filters['price_max'] }}" class="kk-field">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Min. oppervlakte (m²)</span>
                <input type="number" name="surface_min" min="0" step="5" value="{{ $filters['surface_min'] }}" class="kk-field">
            </label>

            <div class="flex flex-wrap items-end justify-between gap-4 sm:col-span-2 lg:col-span-1">
                <label class="inline-flex cursor-pointer items-center gap-2.5 pb-1 text-sm text-ink">
                    <input type="checkbox" name="furnished" value="1" @checked($filters['furnished']) class="kk-check">
                    Gemeubeld
                </label>
                <button type="submit" data-magnetic="0.2" class="kk-cta kk-cta--ink">
                    Zoek
                    <span class="kk-cta-chip" aria-hidden="true">
                        <svg class="kk-cta-arrow kk-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg class="kk-cta-arrow kk-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
            </div>
        </form>

        {{-- Resultaten --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-ink/55">{{ $rooms->total() }} {{ $rooms->total() === 1 ? 'kot' : 'koten' }} gevonden</p>

            <div class="flex items-center gap-5">
                @if ($filters['q'] || $filters['type'] || $filters['price_min'] || $filters['price_max'] || $filters['surface_min'] || $filters['furnished'])
                    <a href="{{ route('rooms.index') }}" class="text-sm text-ink/55 underline hover:text-ink">Filters wissen</a>
                @endif

                {{-- Grid / lijst toggle — view leeft in de URL, paginatie reset --}}
                @php $viewQuery = collect(request()->query())->except('page', 'view'); @endphp
                <nav class="flex overflow-hidden rounded-lg border border-ink/15" aria-label="Weergave">
                    @foreach (['grid' => 'Grid', 'list' => 'Lijst', 'map' => 'Kaart'] as $view => $label)
                        <a href="{{ route('rooms.index', $viewQuery->put('view', $view)->all()) }}"
                           @if ($filters['view'] === $view) aria-current="true" @endif
                           class="px-3.5 py-1.5 text-[0.7rem] font-medium uppercase tracking-[0.12em] transition-colors {{ $filters['view'] === $view ? 'bg-ink text-white' : 'bg-white text-ink-soft hover:text-ink' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        @if ($filters['view'] === 'map')
            {{-- Kaartweergave: kaart links (sticky), koten ernaast. Markers tonen
                 álle gefilterde koten; de kolom ernaast pagineert gewoon mee. --}}
            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <div class="overflow-hidden rounded-2xl border border-ink/10">
                        <x-rooms-map :buildings="$mapBuildings" default-city="hasselt" height="clamp(24rem, calc(100vh - 9rem), 56rem)" />
                    </div>
                </div>

                <div>
                    @if ($rooms->isNotEmpty())
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            @foreach ($rooms as $room)
                                <x-koten-card :room="$room" />
                            @endforeach
                        </div>
                        <div class="mt-10">
                            {{ $rooms->links('components.kk-pagination') }}
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-ink/15 py-20 text-center">
                            <p class="text-lg font-medium">Geen koten gevonden</p>
                            <p class="mt-2 text-sm text-ink/55">Pas je filters aan of zoek in een andere stad.</p>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($rooms->isNotEmpty())
            @if ($filters['view'] === 'list')
                <div>
                    @foreach ($rooms as $room)
                        <x-koten-row :room="$room" />
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($rooms as $room)
                        <x-koten-card :room="$room" />
                    @endforeach
                </div>
            @endif

            <div class="mt-12">
                {{ $rooms->links('components.kk-pagination') }}
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-ink/15 py-20 text-center">
                <p class="text-lg font-medium">Geen koten gevonden</p>
                <p class="mt-2 text-sm text-ink/55">Pas je filters aan of zoek in een andere stad.</p>
            </div>
        @endif

        {{-- Kaart onderaan bij grid/lijst — in kaartweergave staat hij al bovenaan --}}
        @if ($filters['view'] !== 'map')
            <div class="mt-16">
                <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
                    <span class="inline-block h-px w-9 bg-accent-500"></span> Op de kaart
                </p>
                <x-rooms-map :buildings="$mapBuildings" default-city="hasselt" />
            </div>
        @endif

    </section>

    <x-footer />

</x-layout>
