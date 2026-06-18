<x-filament-widgets::widget>
    <div class="kk-rise space-y-4">
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Jouw verhuurder</p>

        @foreach ($landlords as $entry)
            @php
                $landlord = $entry['landlord'];
                $initials = collect(explode(' ', trim((string) $landlord->full_name)))
                    ->filter()
                    ->take(2)
                    ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                    ->implode('');
                $rooms = $entry['rooms']->pluck('title')->join(', ');
            @endphp

            <div class="flex flex-col gap-6 rounded-[1.25rem] border border-[#0f172014] bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#00101e] text-base font-medium tracking-[0.02em] text-white">
                        {{ $initials ?: '—' }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-medium leading-tight tracking-[-0.01em] text-[#0f1720]">{{ $landlord->full_name }}</p>
                        <p class="mt-1 truncate text-sm text-[#586573]">Verhuurder van {{ $rooms }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-7">
                    <dl class="grid gap-1.5 text-sm">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 shrink-0 text-[#8a97a6]" />
                            <a href="mailto:{{ $landlord->email }}" class="truncate text-[#0f1720] transition-colors hover:text-[#3a6ea5]">{{ $landlord->email }}</a>
                        </div>
                        @if ($landlord->phone)
                            <div class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-phone" class="h-4 w-4 shrink-0 text-[#8a97a6]" />
                                <a href="tel:{{ $landlord->phone }}" class="text-[#0f1720] transition-colors hover:text-[#3a6ea5]">{{ $landlord->phone }}</a>
                            </div>
                        @endif
                    </dl>

                    <a href="{{ $chatUrl }}"
                       class="group inline-flex h-11 shrink-0 items-center gap-3 rounded-[4px] bg-[#00101e] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                        Stuur bericht
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                            <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
