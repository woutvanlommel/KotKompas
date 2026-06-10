<x-layout title="Koten zoeken — KotKompas" body-class="bg-canvas text-ink">

    <x-public-nav />

    <section class="mx-auto w-full max-w-[88rem] px-5 pb-24 pt-32 sm:px-8">

        <header class="mb-10">
            <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
                <span class="inline-block h-px w-9 bg-accent-500"></span> Aanbod
            </p>
            <h1 class="text-[clamp(2.2rem,5vw,4rem)] font-medium leading-[0.9] tracking-[-0.04em]">
                Koten zoeken
            </h1>
        </header>

        {{-- Filterbalk — GET zodat filters in de URL leven (deelbaar, paginatie-vriendelijk). --}}
        <form method="GET" action="{{ route('rooms.index') }}"
              class="mb-12 grid grid-cols-1 gap-5 rounded-2xl border border-ink/10 bg-white/60 p-5 sm:grid-cols-2 lg:grid-cols-4">

            <label class="lg:col-span-2">
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Waar zoek je?</span>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Stad, postcode of trefwoord…"
                       class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Type</span>
                <select name="type" class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
                    <option value="">Alle types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>
                            {{ \Illuminate\Support\Str::of($type)->replace('_', ' ')->ucfirst() }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Sorteer</span>
                <select name="sort" class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Nieuwste eerst</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Prijs laag → hoog</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Prijs hoog → laag</option>
                    <option value="surface_desc" @selected($filters['sort'] === 'surface_desc')>Grootste eerst</option>
                </select>
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Min. prijs (€)</span>
                <input type="number" name="price_min" min="0" step="50" value="{{ $filters['price_min'] }}"
                       class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Max. prijs (€)</span>
                <input type="number" name="price_max" min="0" step="50" value="{{ $filters['price_max'] }}"
                       class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
            </label>

            <label>
                <span class="mb-2 block text-[0.625rem] font-medium uppercase tracking-[0.14em] text-ink/55">Min. oppervlakte (m²)</span>
                <input type="number" name="surface_min" min="0" step="5" value="{{ $filters['surface_min'] }}"
                       class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm outline-none focus:border-ink/40">
            </label>

            <div class="flex items-end gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="furnished" value="1" @checked($filters['furnished'])
                           class="h-4 w-4 rounded border-ink/30">
                    Gemeubeld
                </label>
                <button type="submit" class="kk-cta kk-cta--ink ml-auto">Zoek</button>
            </div>
        </form>

        {{-- Kaart --}}
        @if ($mapBuildings->isNotEmpty())
            <div class="mb-12">
                <x-rooms-map :buildings="$mapBuildings" />
            </div>
        @endif

        {{-- Resultaten --}}
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-ink/55">{{ $rooms->total() }} {{ \Illuminate\Support\Str::plural('kot', $rooms->total()) }} gevonden</p>
            @if ($filters['q'] || $filters['type'] || $filters['price_min'] || $filters['price_max'] || $filters['surface_min'] || $filters['furnished'])
                <a href="{{ route('rooms.index') }}" class="text-sm text-ink/55 underline hover:text-ink">Filters wissen</a>
            @endif
        </div>

        @if ($rooms->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($rooms as $room)
                    <x-koten-card :room="$room" />
                @endforeach
            </div>

            <div class="mt-12">
                {{ $rooms->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-ink/15 py-20 text-center">
                <p class="text-lg font-medium">Geen koten gevonden</p>
                <p class="mt-2 text-sm text-ink/55">Pas je filters aan of zoek in een andere stad.</p>
            </div>
        @endif

    </section>

    <x-footer />

</x-layout>
