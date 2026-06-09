@php
    $facilities = $room->facilities->groupBy('category');
@endphp

<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900">Faciliteiten</h2>
        <button wire:click="mountAction('editFacilities')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Bewerken
        </button>
    </div>

    @if ($facilities->isNotEmpty())
        <div class="space-y-5">
            @foreach ($facilities as $category => $items)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $category }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($items as $facility)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                                <svg class="w-3.5 h-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                {{ $facility->name }}
                                @if ($facility->pivot->description)
                                    <span class="text-gray-400">&mdash; {{ $facility->pivot->description }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-400 italic">Geen faciliteiten toegevoegd.</p>
    @endif
</div>
