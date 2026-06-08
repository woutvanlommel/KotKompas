@if ($room->description)
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Beschrijving</h2>
        <div class="rich-content text-sm text-gray-600">
            {!! $room->description !!}
        </div>
    </div>
@endif
