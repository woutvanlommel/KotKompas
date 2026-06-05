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

        {{-- Steel-azure floor: the non-white backdrop the giant word fills against --}}
        <div class="kk-floor" aria-hidden="true"></div>

        {{-- Fixed editorial header — brand left, context right, hairline rule under --}}
        <header class="kk-head">
            <span class="kk-brand">KotKompas<i class="kk-dot">.</i></span>
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

            <aside class="kk-aside" aria-hidden="true">
                <span class="kk-aside-label">(01) &mdash; Welkom</span>
                <p class="kk-aside-text">Het kompas voor studentenkamers in Leuven. Log in om je kot te beheren, je aanvragen op te volgen en rechtstreeks met verhuurders te schakelen.</p>
                <p class="kk-aside-text">Nog geen account? Maak er een en vind in een paar klikken jouw plek &mdash; of zet als verhuurder je kamers online en bereik duizenden studenten.</p>
            </aside>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
