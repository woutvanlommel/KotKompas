@props(['room'])

@php
    $typeLabels = [
        'studio' => 'Studio',
        'one_bedroom' => '1 slaapkamer',
        'two_bedroom' => '2 slaapkamers',
        'three_bedroom' => '3 slaapkamers',
        'four_bedroom' => '4 slaapkamers',
        'five_plus_bedroom' => '5+ slaapkamers',
    ];
    $type = $typeLabels[$room->type] ?? $room->type;
    $photo = $room->getFirstMediaUrl('rooms') ?: null;
    $city = $room->building?->city ?? 'Kot';
    $price = number_format((float) $room->price_per_month, 0, ',', '.');

    $tags = array_filter([
        $room->surface_m2 ? $room->surface_m2 . ' m²' : null,
        $room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld',
        $room->available_from ? 'Vanaf ' . $room->available_from->format('m/Y') : null,
    ]);
@endphp

{{-- Display card: image-dominant. City top-left · price top-right · pixel glyph bottom-left ·
     spec tags reveal on hover (always shown on touch). data-card-cursor = the hover affordance. --}}
<a href="{{ route('contact') }}" data-card-cursor
   class="kk-koten group relative block aspect-[4/5] overflow-hidden rounded-[1.4rem] bg-primary-900">

    {{-- Image --}}
    @if ($photo)
        <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy" data-parallax
             class="absolute inset-0 h-[112%] w-full object-cover transition-[transform,filter] duration-[800ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.04] group-hover:brightness-[0.72]">
    @else
        <div class="absolute inset-0 flex items-center justify-center bg-primary-800 text-white/15">
            <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    @endif

    {{-- top scrim --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 h-36 bg-linear-to-b from-primary-900/75 via-primary-900/25 to-transparent"></div>
    {{-- bottom scrim --}}
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-linear-to-t from-primary-900/80 via-primary-900/20 to-transparent opacity-70 transition-opacity duration-500 group-hover:opacity-100"></div>

    {{-- City (title) top-left + price top-right --}}
    <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-3 p-6 text-white">
        <div class="min-w-0">
            <h3 class="text-2xl font-medium leading-none tracking-[-0.03em]">{{ $city }}</h3>
            <p class="mt-2 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-white/60">{{ $type }}</p>
        </div>
        <span class="kk-serif shrink-0 text-2xl leading-none text-white tabular-nums">€{{ $price }}<span class="ml-0.5 align-top text-[0.65rem] text-white/55">/m</span></span>
    </div>

    {{-- Pixel glyph bottom-left (brand mark) — desktop only, hides on hover --}}
    <div class="absolute bottom-6 left-6 hidden grid-cols-3 gap-1 opacity-80 transition-opacity duration-300 group-hover:opacity-0 sm:grid" aria-hidden="true">
        @for ($i = 0; $i < 9; $i++)
            <span class="h-1 w-1 rounded-[1px] bg-white"></span>
        @endfor
    </div>

    {{-- Reveal: title + spec tags + Bekijk (always on touch, hover on desktop) --}}
    <div class="absolute inset-x-0 bottom-0 p-6 text-white transition-all duration-300 ease-out sm:translate-y-3 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100">
        @if ($room->title)
            <p class="mb-3 max-w-[26ch] text-sm leading-snug text-white/85">{{ $room->title }}</p>
        @endif
        <ul class="flex flex-wrap gap-1.5">
            @foreach ($tags as $tag)
                <li class="rounded-md border border-white/20 bg-white/10 px-2.5 py-1 text-[0.7rem] text-white backdrop-blur-sm">{{ $tag }}</li>
            @endforeach
        </ul>
        <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-white transition-colors group-hover:text-secondary-300">
            Bekijk kot <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
        </span>
    </div>
</a>
