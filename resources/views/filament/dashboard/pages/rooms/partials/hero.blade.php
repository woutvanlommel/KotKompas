<div class="w-full h-72 rounded-2xl overflow-hidden bg-gray-100 relative shadow-sm">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}"
             alt="{{ $room->title }}"
             class="w-full h-full object-cover">
    @else
        <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-gray-400">
            <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <p class="text-sm font-medium">Geen afbeelding beschikbaar</p>
        </div>
    @endif

    <div class="absolute top-4 right-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm
            {{ $status['bg'] }} {{ $status['text'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
            {{ $status['label'] }}
        </span>
    </div>
</div>
