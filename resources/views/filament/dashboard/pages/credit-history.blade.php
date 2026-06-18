<x-filament-panels::page>
    <a href="{{ \App\Filament\Dashboard\Pages\Credits::getUrl() }}"
        class="inline-flex items-center gap-1.5 text-sm font-medium tracking-[-0.01em] text-[#3a6ea5] transition-colors hover:text-[#002f5b]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Terug naar credits
    </a>

    {{ $this->table }}
</x-filament-panels::page>
