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

    // Spec rail: label → value (always visible, scannable). Trust = transparency.
    $specs = array_filter([
        $room->surface_m2 ? ['label' => 'Oppervlak', 'value' => $room->surface_m2 . ' m²'] : null,
        ['label' => 'Inrichting', 'value' => $room->is_furnished ? 'Gemeubeld' : 'Ongemeubeld'],
        $room->available_from ? ['label' => 'Vanaf', 'value' => $room->available_from->format('m/Y')] : null,
    ]);
@endphp

{{-- Editorial listing dossier: contained photo plate + structured spec slip on a raised surface.
     data-card-cursor (root) → global "Bekijk" pointer label is the hover affordance (no duplicate CTA). --}}
<a href="{{ route('contact') }}" data-card-cursor
   class="kk-koten group relative flex flex-col overflow-hidden rounded-[1.25rem] border border-hairline bg-white p-3 transition-colors duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:border-ink/20 sm:p-4">

    {{-- Photo plate (contained, not full-bleed). Hover zoom lives on the plate so it
         never collides with GSAP's [data-parallax] transform on the inner <img>. --}}
    <div class="relative aspect-[5/4] overflow-hidden rounded-[0.85rem] bg-primary-900 transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.015]">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $room->title ?? $type }}" loading="lazy" data-parallax
                 class="absolute inset-0 h-[120%] w-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-primary-800 text-white/15">
                <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                    <path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif

        {{-- Mono index micro-data (decorative, top-left of plate) --}}
        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-ink/55 px-2.5 py-1 font-mono text-[0.65rem] uppercase tracking-[0.12em] text-white/90 backdrop-blur-sm">
            <span class="h-1 w-1 rounded-full bg-accent-500"></span>{{ $type }}
        </span>

        {{-- Type as a quiet caption, bottom-left over a minimal scrim --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-primary-900/55 to-transparent"></div>
    </div>

    {{-- Spec slip on the warm surface --}}
    <div class="px-1.5 pb-1.5 pt-4 sm:px-2 sm:pt-5">

        {{-- City micro-label + price as the hero numeral (two-tone: ink + accent /m) --}}
        <div class="flex items-end justify-between gap-4">
            <div class="min-w-0">
                <p class="flex items-center gap-2 text-[0.65rem] font-medium uppercase tracking-[0.16em] text-ink-soft">
                    <span class="inline-block h-px w-5 bg-accent-500"></span>{{ $city }}
                </p>
                <h3 class="mt-2 truncate text-[1.05rem] font-medium leading-tight tracking-tight text-ink">
                    <span class="bg-linear-to-r from-accent-500 to-accent-500 bg-[length:0%_1.5px] bg-left-bottom bg-no-repeat pb-0.5 transition-[background-size] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:bg-[length:100%_1.5px]">
                        {{ $room->title ?? $type }}
                    </span>
                </h3>
            </div>
            <p class="shrink-0 text-right font-mono text-[1.75rem] font-medium leading-none tracking-[-0.04em] text-ink tabular-nums">
                €{{ $price }}<span class="ml-0.5 align-top text-[0.7rem] tracking-normal text-accent-500">/m</span>
            </p>
        </div>

        {{-- Hairline-divided metadata rail (label over value) — always visible --}}
        @if (count($specs))
            <dl class="mt-5 grid grid-cols-3 divide-x divide-hairline border-t border-hairline pt-4 text-left">
                @foreach ($specs as $spec)
                    <div class="px-3 first:pl-0 last:pr-0">
                        <dt class="text-[0.6rem] font-medium uppercase tracking-[0.14em] text-ink-soft">{{ $spec['label'] }}</dt>
                        <dd class="mt-1 text-[0.8rem] font-medium tracking-tight text-ink">{{ $spec['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</a>
