<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-gray-900">Beschrijving</h2>
        <button wire:click="mountAction('editDescription')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Bewerken
        </button>
    </div>
    @if ($room->description)
        <div class="rich-content text-sm text-gray-600">
            {!! $room->description !!}
        </div>
    @else
        <p class="text-sm text-gray-400 italic">Geen beschrijving toegevoegd.</p>
    @endif
</div>
