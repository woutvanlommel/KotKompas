@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <div class="fi-simple-layout kk-auth">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        {{-- Background, borrowed from ep1's auth hero: the Antwerp Grote Markt photo washed
             under deep navy (opacity + overlay blend) with a gradient that anchors the bottom
             so the giant switch word + form panel stay legible. Static — no canvas/JS. --}}
        <div class="kk-bg" aria-hidden="true">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/hero-test.jpg') }}');"></div>
            {{-- scrim: deep navy anchors the bottom-left (giant word + panel), eases toward the
                 bright top-right so the photo keeps its warm daylight instead of going flat-blue --}}
            <div class="absolute inset-0 bg-linear-to-tr from-primary-900 via-primary-900/75 to-primary-900/20"></div>
        </div>


        {{-- Fixed editorial header — brand left (links home), escape links right --}}
        <header class="fixed inset-x-0 top-0 z-10 flex items-center justify-between gap-4 px-[clamp(1.5rem,4vw,3.5rem)] py-[clamp(1rem,2.25vw,1rem)]">
            <a href="{{ url('/') }}" class="inline-flex" aria-label="KotKompas — naar de homepagina"><img src="{{ asset('/img/400pxX100pxWoordLogoLiggendZwart.png') }}" alt="KotKompas" class="h-18 w-auto [filter:brightness(0)_invert(1)]" /></a>
            <nav class="flex items-center gap-5 text-sm font-medium text-white/85" aria-label="Terug naar de site">
                <a href="{{ url('/') }}" class="transition-colors hover:text-white">Home</a>
                <a href="{{ route('rooms.index') }}" class="transition-colors hover:text-white">Koten</a>
            </nav>
        </header>

        <div class="fi-simple-main-ctn kk-stage">
            @if (($hasTopbar ?? true) && filament()->auth()->check())
                <div class="fi-simple-layout-header">
                    @if (filament()->hasDatabaseNotifications())
                        @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                            'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                        ])
                    @endif

                    @if (filament()->hasUserMenu())
                        @livewire(Filament\Livewire\SimpleUserMenu::class)
                    @endif
                </div>
            @endif

            <main class="fi-simple-main">
                {{ $slot }}
            </main>

            <aside lang="nl" class="max-md:hidden shrink-0 self-start max-w-xs pt-[0.4rem] text-right font-sans text-white [text-shadow:0_1px_18px_rgba(0,16,30,0.9)]">
                {{-- eyebrow: tracked-caps kicker + single accent dash marks this as KotKompas's pitch --}}
                <p class="flex items-center justify-end gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/70">
                    Waarom KotKompas
                    <span class="inline-block h-px w-6 bg-accent" aria-hidden="true"></span>
                </p>

                {{-- three distilled value-props — hairline-divided, scannable in one pass --}}
                <dl class="mt-5 divide-y divide-white/20">
                    <div class="py-3">
                        <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Direct</dt>
                        <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Huur rechtstreeks van de eigenaar, zonder makelaarskosten of tussenpersoon.</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Overzicht</dt>
                        <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Zoek, vergelijk en plan bezichtigingen in jouw stad, alles op één plek.</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Zeker</dt>
                        <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Geen makelaarskosten of verborgen kosten — volledig transparant.</dd>
                    </div>
                </dl>
            </aside>
        </div>

        {{-- The giant switch word is emitted by Filament inside the form panel (subheading).
             Lift it to be a sibling of the panel so the translucent panel (z-index:2) can sit
             OVER it (a child can't paint behind its parent's own background). It's position:fixed,
             so moving it doesn't shift it on screen. --}}
        <script>
            (() => {
                const stage = document.querySelector('.kk-stage');
                if (!stage) return;

                const relocate = () => {
                    // Lift the giant switch word out so the panel (z-index:2) paints over it.
                    // Each Livewire morph re-emits a fresh word inside the panel; keep ONE canonical
                    // node in the stage and drop the morph copies — otherwise they stack, doubling
                    // the hover/entrance animation.
                    const words = [...document.querySelectorAll('.kk-switch')];
                    const keep = words.find((w) => w.parentElement === stage) || words[0];
                    if (keep) {
                        words.forEach((w) => { if (w !== keep) w.remove(); });
                        if (keep.parentElement !== stage) stage.appendChild(keep);
                    }

                    // login: move "wachtwoord vergeten?" beside the submit so they share one row
                    const forgot = document.querySelector('.kk-forgot');
                    const actions = document.querySelector('.fi-sc-actions');
                    if (forgot && actions && forgot.parentElement !== actions) actions.appendChild(forgot);
                };

                relocate();
                // Livewire re-renders (typing / validation) re-inject these nodes inside the
                // panel — without this the switch word jumps on top of the fields. Re-run on morph.
                new MutationObserver(relocate).observe(stage, { childList: true, subtree: true });
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
