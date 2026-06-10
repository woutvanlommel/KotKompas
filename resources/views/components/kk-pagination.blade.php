
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginering" class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-ink/55">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} van {{ $paginator->total() }} koten
        </p>

        <div class="flex overflow-hidden rounded-lg border border-ink/15 text-[0.7rem] font-medium uppercase tracking-[0.12em]">
            {{-- Vorige --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-ink/25" aria-disabled="true">←</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Vorige pagina"
                   class="bg-white px-3 py-1.5 text-ink-soft transition-colors hover:text-ink">←</a>
            @endif

            
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="border-l border-ink/15 px-3 py-1.5 text-ink/40">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="border-l border-ink/15 bg-ink px-3.5 py-1.5 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ga naar pagina {{ $page }}"
                               class="border-l border-ink/15 bg-white px-3.5 py-1.5 text-ink-soft transition-colors hover:text-ink">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Volgende pagina"
                   class="border-l border-ink/15 bg-white px-3 py-1.5 text-ink-soft transition-colors hover:text-ink">→</a>
            @else
                <span class="border-l border-ink/15 px-3 py-1.5 text-ink/25" aria-disabled="true">→</span>
            @endif
        </div>
    </nav>
@endif
