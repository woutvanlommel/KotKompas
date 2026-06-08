@php
    $coverMedia = $room->getFirstMedia('cover');
    $allMedia   = $room->getMedia('gallery');
    $perPage    = 8;
    $total      = $allMedia->count();
    $totalAll   = ($coverMedia ? 1 : 0) + $total;
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($this->galleryPage, $totalPages);
    $slice      = $allMedia->slice(($page - 1) * $perPage, $perPage);
@endphp

<div x-data="{ selectMode: false, selected: [], coverLoading: null }"
     @gallery-deleted.window="selectMode = false; selected = []"
     class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900">
            Foto's
            @if ($totalAll > 0)
                <span class="ml-1.5 text-sm font-normal text-gray-400">({{ $totalAll }})</span>
            @endif
        </h2>

        <div class="flex items-center gap-2">

            {{-- Selecteer modus knoppen --}}
            <template x-if="selectMode">
                <div class="flex items-center gap-2">
                    <button x-show="selected.length > 0"
                            @click="$wire.mountAction('deleteSelectedGalleryImages', { ids: selected })"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Verwijder (<span x-text="selected.length"></span>)
                    </button>
                    <button @click="selectMode = false; selected = []"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        Annuleren
                    </button>
                </div>
            </template>

            {{-- Normale modus knoppen --}}
            <template x-if="!selectMode">
                <div class="flex items-center gap-2">
                    <button wire:click="mountAction('uploadGallery')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        Toevoegen
                    </button>
                    @if ($total > 0)
                        <button @click="selectMode = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Selecteren
                        </button>
                    @endif
                </div>
            </template>

            {{-- Paginering --}}
            @if ($totalPages > 1)
                <div class="flex items-center gap-1 text-sm text-gray-500 border-l border-gray-200 pl-2 ml-1">
                    <button wire:click="previousGalleryPage"
                            @disabled($page <= 1)
                            class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <span class="text-xs w-10 text-center">{{ $page }} / {{ $totalPages }}</span>
                    <button wire:click="nextGalleryPage({{ $totalPages }})"
                            @disabled($page >= $totalPages)
                            class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            @endif

        </div>
    </div>

    {{-- Leeg --}}
    @if (! $coverMedia && $total === 0)
        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
            <svg class="w-10 h-10 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
            </svg>
            <p class="text-sm font-medium">Nog geen foto's</p>
            <p class="text-xs mt-1">Klik op "Toevoegen" om foto's te uploaden.</p>
        </div>
    @endif

    {{-- Grid --}}
    @if ($coverMedia || $total > 0)
        <div class="relative">
            <div wire:loading wire:target="previousGalleryPage,nextGalleryPage"
                 class="absolute inset-0 z-10 bg-white/60 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                {{-- Cover --}}
                @if ($coverMedia)
                    <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 group">
                        <img src="{{ $coverMedia->getUrl('webp') ?: $coverMedia->getUrl() }}"
                             alt="{{ $coverMedia->name }}"
                             class="w-full h-full object-cover">

                        <div class="absolute top-2 left-2 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-amber-400 text-white shadow-sm">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Cover
                        </div>

                        <button x-show="!selectMode"
                                wire:click="mountAction('deleteGalleryImage', { mediaId: {{ $coverMedia->id }} })"
                                class="absolute top-2 right-2 w-7 h-7 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                {{-- Gallery items --}}
                @foreach ($slice as $media)
                    <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 group"
                         :class="{ 'cursor-pointer': selectMode }"
                         @click="if (selectMode) { selected.includes({{ $media->id }}) ? selected = selected.filter(id => id !== {{ $media->id }}) : selected.push({{ $media->id }}) }">

                        <img src="{{ $media->getUrl('webp') ?: $media->getUrl() }}"
                             alt="{{ $media->name }}"
                             class="w-full h-full object-cover transition duration-200"
                             :class="{ 'brightness-75': selectMode && selected.includes({{ $media->id }}) }">

                        <div x-show="selectMode"
                             class="absolute top-2 left-2 pointer-events-none">
                            <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition"
                                 :class="selected.includes({{ $media->id }}) ? 'bg-primary-600 border-primary-600' : 'bg-white/80 border-gray-300'">
                                <svg x-show="selected.includes({{ $media->id }})"
                                     class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                        </div>

                        <div x-show="coverLoading === {{ $media->id }}"
                             x-cloak
                             class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                        </div>

                        <div x-show="!selectMode" class="contents">
                            <button @click.stop="coverLoading = {{ $media->id }}; $wire.mountAction('setCover', { mediaId: {{ $media->id }} }).finally(() => coverLoading = null)"
                                    title="Stel in als cover"
                                    class="absolute top-2 left-2 w-7 h-7 rounded-full bg-black/50 hover:bg-amber-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                            <button @click.stop="$wire.mountAction('deleteGalleryImage', { mediaId: {{ $media->id }} })"
                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    @endif

</div>
