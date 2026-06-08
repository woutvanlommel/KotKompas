<footer class="border-t border-base-twee-300 bg-base-een-200">
    <div class="mx-auto w-full max-w-6xl px-5 py-12 sm:px-6">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <span class="text-lg font-semibold tracking-tight text-primary-900">KotKompas</span>
                <p class="mt-2 max-w-xs text-sm leading-relaxed text-base-een-700">
                    Huur rechtstreeks van de eigenaar — zonder makelaarskosten.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-base-een-700">Ontdek</p>
                <ul class="mt-3 space-y-2 text-sm text-base-een-800">
                    <li><a href="{{ url('/') }}" class="hover:text-primary-600">Home</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-primary-600">Veelgestelde vragen</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-primary-600">Contact</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-base-een-700">Account</p>
                <ul class="mt-3 space-y-2 text-sm text-base-een-800">
                    <li><a href="{{ url('/dashboard/login') }}" class="hover:text-primary-600">Inloggen</a></li>
                    <li><a href="{{ url('/dashboard/register') }}" class="hover:text-primary-600">Registreren</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-base-een-700">Contact</p>
                <ul class="mt-3 space-y-2 text-sm text-base-een-800">
                    <li><a href="mailto:hallo@kotkompas.be" class="hover:text-primary-600">hallo@kotkompas.be</a></li>
                    <li><a href="/privacy" class="hover:text-primary-600">Privacybeleid</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 border-t border-base-twee-300 pt-6 text-xs text-base-een-700">
            © {{ date('Y') }} KotKompas. Alle rechten voorbehouden.
        </div>
    </div>
</footer>
