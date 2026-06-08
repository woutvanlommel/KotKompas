<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900">Status & Huurder</h2>
        <button wire:click="mountAction('updateStatus')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Status wijzigen
        </button>
    </div>

    <div class="flex items-center gap-2 mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold
            {{ $status['bg'] }} {{ $status['text'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
            {{ $status['label'] }}
        </span>
    </div>

    <div class="border-t border-gray-100 pt-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-700">Huurder</p>
            <div class="flex items-center gap-2">
                @if ($room->tenant)
                    <button wire:click="mountAction('linkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                        Wijzigen
                    </button>
                    <button wire:click="mountAction('unlinkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Ontkoppelen
                    </button>
                @else
                    <button wire:click="mountAction('linkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        Huurder koppelen
                    </button>
                @endif
            </div>
        </div>

        @if ($room->tenant)
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <span class="text-sm font-semibold text-blue-700">
                        {{ strtoupper(substr($room->tenant->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $room->tenant->name }}</p>
                    <a href="mailto:{{ $room->tenant->email }}" class="text-xs text-gray-500 hover:text-gray-700">{{ $room->tenant->email }}</a>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-400 italic">Geen huurder gekoppeld.</p>
        @endif
    </div>
</div>
