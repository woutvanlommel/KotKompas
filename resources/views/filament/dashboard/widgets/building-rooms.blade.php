@php
    $building = $getRecord();
    $rooms = $building->rooms;

    $statusLabels = [
        'available' => 'Beschikbaar',
        'rented'    => 'Verhuurd',
        'archived'  => 'Gearchiveerd',
    ];
    $statusColors = [
        'available' => 'bg-success-100 text-success-700',
        'rented'    => 'bg-primary-100 text-primary-700',
        'archived'  => 'bg-gray-100 text-gray-500',
    ];
@endphp

@if ($rooms->isEmpty())
    <p class="px-4 py-3 text-sm text-gray-500">Geen koten gevonden.</p>
@else
    <div class="overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-4 py-2">Kamer</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Huurder</th>
                    <th class="px-4 py-2">E-mail huurder</th>
                    <th class="px-4 py-2">Huurprijs/mnd</th>
                    <th class="px-4 py-2">Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($rooms as $room)
                    @php
                        $statusLabel = $statusLabels[$room->status] ?? $room->status;
                        $statusColor = $statusColors[$room->status] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                            {{ $room->title ?? 'Kamer '.$room->room_number }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $room->tenant?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $room->tenant?->email ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $room->price_per_month !== null
                                ? '€ '.number_format($room->price_per_month, 2, ',', '.')
                                : '—' }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            @if ($room->score !== null)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $room->score >= 4.0 ? 'bg-success-100 text-success-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ number_format($room->score, 1, ',', '.') }} ({{ $room->reviews_count }})
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Geen</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
