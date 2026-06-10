@props(['room'])

@php
    $cover        = $room->getFirstMedia('cover') ?? $room->getFirstMedia('rooms');
    $galleryMedia = $room->getMedia('gallery');

    $allMedia = $cover
        ? collect([$cover])->concat($galleryMedia)
        : collect($galleryMedia);

    $photos = $allMedia->map(fn($m) => [
        'url'   => $m->getUrl('webp') ?: $m->getUrl(),
        'thumb' => $m->getUrl('thumb') ?: $m->getUrl(),
        'alt'   => $m->name ?? ($room->title ?? 'Foto'),
    ])->values()->toArray();

    $total      = count($photos);
    $thumbCount = min(4, max(0, $total - 1));
    $extra      = max(0, $total - 5);
@endphp

@if ($total === 0)
    <div class="mt-8 flex h-64 items-center justify-center rounded-2xl border border-dashed border-hairline bg-canvas-deep">
        <div class="text-center text-ink/40">
            <x-heroicon-o-photo class="mx-auto h-10 w-10" aria-hidden="true" />
            <p class="mt-3 text-sm font-medium">Geen foto's beschikbaar</p>
        </div>
    </div>
@else
    <div class="mt-8"
         x-data="{
             open:    false,
             current: 0,
             photos:  {{ Js::from($photos) }},
             openAt(i) { this.current = i; this.open = true; document.body.style.overflow = 'hidden'; },
             close()   { this.open = false; document.body.style.overflow = ''; },
             prev()    { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
             next()    { this.current = (this.current + 1) % this.photos.length; }
         }"
         @keydown.arrow-left.window="if (open) prev()"
         @keydown.arrow-right.window="if (open) next()"
         @keydown.escape.window="if (open) close()">

        {{-- Desktop grid --}}
        <div class="hidden overflow-hidden rounded-2xl md:grid md:h-[30rem]
                    {{ $thumbCount > 0 ? 'md:grid-cols-4 md:grid-rows-2 md:gap-2' : '' }}">

            {{-- Cover --}}
            <button type="button" @click="openAt(0)"
                    class="{{ $thumbCount > 0 ? 'col-span-2 row-span-2' : 'col-span-4 row-span-2' }}
                           group relative w-full overflow-hidden bg-primary-900 focus:outline-none">
                <img src="{{ $photos[0]['url'] }}" alt="{{ $photos[0]['alt'] }}" loading="eager"
                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.03]">
                <span class="pointer-events-none absolute inset-0 bg-primary-900/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
            </button>

            {{-- Thumbnails --}}
            @for ($i = 1; $i <= $thumbCount; $i++)
                @php $isLast = ($i === $thumbCount) && ($extra > 0); @endphp
                <button type="button" @click="openAt({{ $i }})"
                        class="group relative w-full overflow-hidden bg-primary-900 focus:outline-none">
                    <img src="{{ $photos[$i]['thumb'] }}" alt="{{ $photos[$i]['alt'] }}" loading="lazy"
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.03]">
                    <span class="pointer-events-none absolute inset-0 bg-primary-900/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                    @if ($isLast)
                        <div class="absolute inset-0 flex items-center justify-center bg-primary-900/55 backdrop-blur-[2px]">
                            <span class="text-2xl font-medium text-white">+{{ $extra }}</span>
                        </div>
                    @endif
                </button>
            @endfor
        </div>

        {{-- Mobiel --}}
        <div class="relative overflow-hidden rounded-2xl md:hidden" style="height:18rem;">
            <img src="{{ $photos[0]['url'] }}" alt="{{ $photos[0]['alt'] }}" loading="eager"
                 class="absolute inset-0 h-full w-full object-cover">
            @if ($total > 1)
                <button type="button" @click="openAt(0)"
                        class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-xl bg-black/60 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <x-heroicon-o-photo class="h-4 w-4 shrink-0" aria-hidden="true" />
                    Bekijk alle {{ $total }} foto's
                </button>
            @endif
        </div>

        {{-- Lightbox --}}
        <div x-show="open" style="display:none"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/95"
             @click.self="close()">

            {{-- Teller --}}
            <div class="absolute left-1/2 top-5 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-sm text-white backdrop-blur-sm">
                <span x-text="current + 1"></span>&thinsp;/&thinsp;<span x-text="photos.length"></span>
            </div>

            {{-- Sluiten --}}
            <button type="button" @click="close()"
                    class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/25"
                    aria-label="Sluiten">
                <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
            </button>

            {{-- Afbeelding --}}
            <img :src="photos[current]?.url" :alt="photos[current]?.alt"
                 class="max-h-[88vh] max-w-[88vw] select-none object-contain">

            {{-- Vorige --}}
            <button type="button" @click.stop="prev()" x-show="photos.length > 1"
                    class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/25 sm:left-4"
                    aria-label="Vorige foto">
                <x-heroicon-o-chevron-left class="h-5 w-5" aria-hidden="true" />
            </button>

            {{-- Volgende --}}
            <button type="button" @click.stop="next()" x-show="photos.length > 1"
                    class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/25 sm:right-4"
                    aria-label="Volgende foto">
                <x-heroicon-o-chevron-right class="h-5 w-5" aria-hidden="true" />
            </button>

            {{-- Thumbnail strip --}}
            @if ($total > 1)
                <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-1.5 overflow-x-auto px-4"
                     style="max-width: min(100vw - 2rem, 40rem);">
                    @foreach ($photos as $i => $photo)
                        <button type="button" @click.stop="current = {{ $i }}"
                                class="relative h-12 w-12 shrink-0 overflow-hidden rounded-md transition-opacity"
                                :class="current === {{ $i }} ? 'opacity-100 ring-2 ring-white' : 'opacity-50 hover:opacity-80'">
                            <img src="{{ $photo['thumb'] }}" alt="" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

        </div>

    </div>
@endif
