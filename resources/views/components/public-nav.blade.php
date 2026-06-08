@props(['overHero' => false])

<header
    @class([
        'kk-nav sticky top-0 z-30 transition-colors duration-300',
        'border-b border-hairline bg-canvas/85 text-ink backdrop-blur-md' => ! $overHero,
    ])
    @if ($overHero) data-adaptive @endif
>
    <nav class="mx-auto flex w-full max-w-[88rem] items-center justify-between px-5 py-4 sm:px-8">
        <a href="{{ url('/') }}" class="kk-nav-strong inline-flex items-center gap-2 text-base font-medium tracking-tight text-current">
            <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
            KotKompas
        </a>

        {{-- Desktop --}}
        <div class="hidden items-center gap-7 sm:flex">
            <a href="{{ route('faq') }}" class="kk-nav-link text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink">FAQ</a>
            <a href="{{ route('contact') }}" class="kk-nav-link text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink">Contact</a>
            <a href="{{ url('/dashboard/login') }}" class="kk-nav-link text-xs font-medium uppercase tracking-[0.12em] text-ink-soft transition hover:text-ink">Inloggen</a>
            <a href="{{ url('/dashboard/register') }}" class="kk-nav-strong group inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-[0.12em] text-current">
                Registreren
                <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span>
            </a>
        </div>

        {{-- Mobile --}}
        <details class="group relative sm:hidden">
            <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-current [&::-webkit-details-marker]:hidden">
                <svg class="h-6 w-6 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
                <svg class="hidden h-6 w-6 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
                <span class="sr-only">Menu</span>
            </summary>
            <div class="absolute right-0 z-40 mt-3 w-56 overflow-hidden rounded-lg border border-hairline bg-canvas p-2 text-ink shadow-xl">
                <a href="{{ route('faq') }}" class="block rounded-md px-3 py-3 text-sm font-medium text-ink hover:bg-canvas-deep">FAQ</a>
                <a href="{{ route('contact') }}" class="block rounded-md px-3 py-3 text-sm font-medium text-ink hover:bg-canvas-deep">Contact</a>
                <a href="{{ url('/dashboard/login') }}" class="block rounded-md px-3 py-3 text-sm font-medium text-ink hover:bg-canvas-deep">Inloggen</a>
                <a href="{{ url('/dashboard/register') }}" class="mt-1 block rounded-md bg-ink px-3 py-3 text-center text-sm font-medium text-canvas">Registreren</a>
            </div>
        </details>
    </nav>
</header>
