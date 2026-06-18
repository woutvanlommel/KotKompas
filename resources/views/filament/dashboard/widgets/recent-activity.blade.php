<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Recente activiteit</p>

        @if ($items->isEmpty())
            {{-- Zero-state recedes — one hairline guidance line. --}}
            <p class="mt-6 text-sm tracking-[-0.01em] text-[#586573]">Nog geen activiteit. Reviews en nieuwe verhuringen verschijnen hier.</p>
        @else
            <ul class="mt-6 grid gap-x-12 lg:grid-cols-2">
                @foreach ($items as $item)
                    <li class="kk-act-row">
                        <span class="kk-act-icon kk-act-icon--{{ $item['type'] }}" aria-hidden="true">
                            @if ($item['type'] === 'review')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m12 3 2.6 5.3 5.9.9-4.2 4.1 1 5.8L12 16.9 6.7 19.7l1-5.8L3.5 9.7l5.9-.9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h7m-4-4 4 4-4 4M14 4l6 8-6 8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium tracking-[-0.01em] text-[#0f1720]">
                                {{ $item['title'] }}
                                @if ($item['meta'])
                                    <span class="kk-act-meta">{{ $item['meta'] }}</span>
                                @endif
                            </p>
                            <p class="truncate text-xs tracking-[-0.01em] text-[#586573]">
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" class="kk-act-link">{{ $item['subtitle'] }}</a>
                                @else
                                    {{ $item['subtitle'] }}
                                @endif
                            </p>
                        </div>

                        <time class="kk-act-time" datetime="{{ $item['date']->toIso8601String() }}">{{ $item['date']->diffForHumans(short: true) }}</time>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
