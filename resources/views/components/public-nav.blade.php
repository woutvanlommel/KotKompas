<header class="sticky top-0 z-30 border-b border-base-twee-300/70 bg-base-een-100/90 backdrop-blur">
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-3 sm:px-6">
        <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-primary-900">KotKompas</a>
        <div class="flex items-center gap-1 sm:gap-2">
            <a href="{{ route('faq') }}" class="hidden rounded-md px-3 py-2 text-sm font-medium text-base-een-800 hover:bg-base-een-300 sm:inline-block">FAQ</a>
            <a href="{{ route('contact') }}" class="hidden rounded-md px-3 py-2 text-sm font-medium text-base-een-800 hover:bg-base-een-300 sm:inline-block">Contact</a>
            <a href="{{ url('/dashboard/login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-primary-700 hover:bg-base-een-300">Inloggen</a>
            <a href="{{ url('/dashboard/register') }}" class="rounded-md bg-accent-500 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-accent-600">Registreren</a>
        </div>
    </nav>
</header>
