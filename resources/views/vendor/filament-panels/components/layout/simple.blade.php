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


        {{-- Fixed editorial header — brand left --}}
        <header class="fixed inset-x-0 top-0 z-10 flex items-start justify-between gap-4 px-[clamp(1.5rem,4vw,3.5rem)] py-[clamp(1.25rem,2.5vw,2rem)]">
            <span class="font-sans text-[1.0625rem] font-medium tracking-[-0.02em] text-base-twee-900">KotKompas<i class="not-italic text-accent">.</i></span>
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

            <aside class="max-md:hidden shrink-0 self-start max-w-[22rem] pt-[0.4rem] text-left font-sans" aria-hidden="true">
                <span class="mb-4 block text-[0.6875rem] uppercase tracking-[0.14em] text-base-twee-700">(01) &mdash; Welkom</span>
                <p class="text-[0.9375rem] leading-[1.55] tracking-[-0.01em] text-base-twee-900">Het kompas voor studentenkamers in Leuven. Log in om je kot te beheren, je aanvragen op te volgen en rechtstreeks met verhuurders te schakelen.</p>
                <p class="mt-[0.9rem] text-[0.9375rem] leading-[1.55] tracking-[-0.01em] text-base-twee-900">Nog geen account? Maak er een en vind in een paar klikken jouw plek &mdash; of zet als verhuurder je kamers online en bereik duizenden studenten.</p>
            </aside>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
