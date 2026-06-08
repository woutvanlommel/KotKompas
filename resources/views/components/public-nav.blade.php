<header class="sticky top-0 z-30 border-b border-base-twee-300/70 bg-base-een-100/90 backdrop-blur">
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-3 sm:px-6">
        <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-primary-900">KotKompas</a>

        {{-- Desktop links --}}
        <div class="hidden items-center gap-2 sm:flex">
            <a href="{{ route('faq') }}" class="rounded-md px-3 py-2 text-sm font-medium text-base-een-800 hover:bg-base-een-300">FAQ</a>
            <a href="{{ route('contact') }}" class="rounded-md px-3 py-2 text-sm font-medium text-base-een-800 hover:bg-base-een-300">Contact</a>
            <a href="{{ url('/dashboard/login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-primary-700 hover:bg-base-een-300">Inloggen</a>
            <a href="{{ url('/dashboard/register') }}" class="rounded-md bg-accent-500 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-accent-600">Registreren</a>
        </div>

        {{-- Mobile menu (no-JS disclosure) --}}
        <details class="group relative sm:hidden">
            <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-md text-primary-900 hover:bg-base-een-300 [&::-webkit-details-marker]:hidden">
                <svg class="h-6 w-6 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
                <svg class="hidden h-6 w-6 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
                <span class="sr-only">Menu</span>
            </summary>
            <div class="absolute right-0 z-40 mt-2 w-52 overflow-hidden rounded-lg border border-base-twee-300 bg-white p-1.5 shadow-lg">
                <a href="{{ route('faq') }}" class="block rounded-md px-3 py-2.5 text-sm font-medium text-base-een-800 hover:bg-base-een-200">FAQ</a>
                <a href="{{ route('contact') }}" class="block rounded-md px-3 py-2.5 text-sm font-medium text-base-een-800 hover:bg-base-een-200">Contact</a>
                <a href="{{ url('/dashboard/login') }}" class="block rounded-md px-3 py-2.5 text-sm font-medium text-primary-700 hover:bg-base-een-200">Inloggen</a>
                <a href="{{ url('/dashboard/register') }}" class="mt-1 block rounded-md bg-accent-500 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-accent-600">Registreren</a>
            </div>
        </details>
    </nav>
</header>
