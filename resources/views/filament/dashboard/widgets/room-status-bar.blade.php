<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Statusverdeling</p>

        @if ($total === 0)
            {{-- Zero-state recedes — one hairline guidance line, no four bare "0" figures. --}}
            <p class="mt-6 text-sm tracking-[-0.01em] text-[#586573]">Nog geen koten toegevoegd.</p>
        @else
            {{-- Segmented hairline-gapped bar — proportional, not a donut. --}}
            <div class="mt-6 flex h-2.5 w-full gap-1">
                @foreach ($segments as $seg)
                    @if ($seg['count'] > 0)
                        <div class="h-full rounded-[2px] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                             style="width: {{ $seg['pct'] }}%; background: {{ $seg['color'] }};"
                             title="{{ $seg['label'] }}: {{ $seg['count'] }}"></div>
                    @endif
                @endforeach
            </div>

            {{-- Legend — the highest-count status owns the card; the rest read as footnotes. --}}
            <dl class="mt-7 grid grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-4">
                @foreach ($segments as $seg)
                    @php $isDominant = $seg['status'] === $dominantStatus; @endphp
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $seg['color'] }};"></span>
                            <dt class="truncate text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $seg['label'] }}</dt>
                        </div>
                        @if ($isDominant)
                            <dd class="mt-1.5 text-[2.5rem] font-medium leading-none tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ $seg['count'] }}</dd>
                        @else
                            <dd class="mt-1.5 text-[1.25rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#586573]">{{ $seg['count'] }}</dd>
                        @endif
                    </div>
                @endforeach
            </dl>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
