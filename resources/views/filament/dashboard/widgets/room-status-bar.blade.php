<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Statusverdeling</p>
        <h3 class="mt-1.5 text-[clamp(1.25rem,1.6vw,1.6rem)] font-medium leading-none tracking-[-0.02em] text-[#0f1720]">Jouw koten per status</h3>

        {{-- Segmented hairline-gapped bar — proportional, not a donut. --}}
        <div class="mt-6 flex h-2.5 w-full gap-1">
            @forelse ($segments as $seg)
                @if ($seg['count'] > 0)
                    <div class="h-full rounded-full transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                         style="width: {{ $seg['pct'] }}%; background: {{ $seg['color'] }};"
                         title="{{ $seg['label'] }}: {{ $seg['count'] }}"></div>
                @endif
            @empty
            @endforelse
            @if ($total === 0)
                <div class="h-full w-full rounded-full bg-[#e1e6ed]"></div>
            @endif
        </div>

        {{-- Legend — counts as figures under micro-caps labels. --}}
        <dl class="mt-7 grid grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-4">
            @foreach ($segments as $seg)
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $seg['color'] }};"></span>
                        <dt class="truncate text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $seg['label'] }}</dt>
                    </div>
                    <dd class="mt-1.5 text-[1.75rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ $seg['count'] }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>
</x-filament-widgets::widget>
