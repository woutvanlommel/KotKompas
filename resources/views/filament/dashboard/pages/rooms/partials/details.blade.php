<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Kenmerken --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-5">Kenmerken</h2>
        <dl class="space-y-4">
            @if ($room->surface_m2)
                <div class="flex items-center justify-between">
                    <dt class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                        Oppervlakte
                    </dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $room->surface_m2 }} m²</dd>
                </div>
            @endif
            <div class="flex items-center justify-between">
                <dt class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    Gemeubileerd
                </dt>
                <dd class="text-sm font-medium text-gray-900">
                    @if ($room->is_furnished)
                        <span class="inline-flex items-center gap-1 text-green-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Ja
                        </span>
                    @else
                        <span class="text-gray-400">Nee</span>
                    @endif
                </dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                    Kosten inbegrepen
                </dt>
                <dd class="text-sm font-medium text-gray-900">
                    @if ($room->costs_included)
                        <span class="inline-flex items-center gap-1 text-green-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Ja
                        </span>
                    @else
                        <span class="text-gray-400">Nee</span>
                    @endif
                </dd>
            </div>
            @if ($room->available_from)
                <div class="flex items-center justify-between">
                    <dt class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                        </svg>
                        Beschikbaar vanaf
                    </dt>
                    <dd class="text-sm font-medium text-gray-900">
                        {{ $room->available_from->format('d/m/Y') }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>


</div>
