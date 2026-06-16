<button
    wire:click.stop="toggle"
    wire:loading.attr="disabled"
    type="button"
    aria-label="{{ $isFavourited ? 'Verwijder uit favorieten' : 'Voeg toe aan favorieten' }}"
    title="{{ $isFavourited ? 'Verwijder uit favorieten' : 'Voeg toe aan favorieten' }}"
    class="{{ $isFavourited
        ? 'bg-white/90 text-red-500 hover:bg-white'
        : 'bg-white/75 text-ink/50 hover:bg-white hover:text-red-500' }}
        group/fav pointer-events-auto flex h-8 w-8 items-center justify-center rounded-full backdrop-blur-sm transition-all duration-200"
>
    <svg
        viewBox="0 0 24 24"
        class="h-4 w-4 transition-transform duration-150 group-hover/fav:scale-110"
        aria-hidden="true"
        fill="{{ $isFavourited ? 'currentColor' : 'none' }}"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
    </svg>
</button>
