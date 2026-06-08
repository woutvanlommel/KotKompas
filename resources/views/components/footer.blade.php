<footer class="border-t border-hairline bg-canvas">
    <div class="mx-auto w-full max-w-[88rem] px-5 pt-16 pb-10 sm:px-8">
        <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                <span class="inline-flex items-center gap-2 text-base font-medium tracking-tight text-ink">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span> KotKompas
                </span>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-ink-soft">
                    Huur rechtstreeks van de eigenaar — zonder makelaarskosten of tussenpersoon.
                </p>
            </div>

            <div>
                <p class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Ontdek</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ink">
                    <li><a href="{{ url('/') }}" class="transition hover:text-accent-600">Home</a></li>
                    <li><a href="{{ route('faq') }}" class="transition hover:text-accent-600">Veelgestelde vragen</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-accent-600">Contact</a></li>
                </ul>
            </div>

            <div>
                <p class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Account</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ink">
                    <li><a href="{{ url('/dashboard/login') }}" class="transition hover:text-accent-600">Inloggen</a></li>
                    <li><a href="{{ url('/dashboard/register') }}" class="transition hover:text-accent-600">Registreren</a></li>
                </ul>
            </div>

            <div>
                <p class="text-[0.7rem] font-medium uppercase tracking-[0.14em] text-ink-soft">Contact</p>
                <ul class="mt-4 space-y-2.5 text-sm text-ink">
                    <li><a href="mailto:hallo@kotkompas.be" class="transition hover:text-accent-600">hallo@kotkompas.be</a></li>
                    <li><a href="/privacy" class="transition hover:text-accent-600">Privacybeleid</a></li>
                </ul>
            </div>
        </div>

        {{-- Oversized wordmark band --}}
        <div class="mt-16 overflow-hidden border-t border-hairline pt-8">
            <p class="select-none text-[18vw] font-medium leading-[0.8] tracking-[-0.05em] text-ink/[0.06] md:text-[14vw]">KotKompas</p>
        </div>

        <div class="mt-6 flex flex-col gap-2 text-xs text-ink-soft sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} KotKompas. Alle rechten voorbehouden.</span>
            <span class="uppercase tracking-[0.14em]">Antwerpen, BE</span>
        </div>
    </div>
</footer>
