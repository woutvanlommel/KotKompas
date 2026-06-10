<?php

namespace App\Providers\Filament;

use App\Filament\Dashboard\Pages\Auth\Login;
use App\Filament\Dashboard\Pages\Auth\Register;
use App\Filament\Dashboard\Pages\Auth\RequestPasswordReset;
use App\Filament\Dashboard\Pages\Auth\ResetPassword;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dashboard')
            ->path('dashboard')
            ->viteTheme('resources/css/filament/dashboard/theme.css')
            ->brandName('KotKompas')
            ->favicon(asset('img/favicon-256.png'))
            ->darkMode(false)
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(app(Vite::class)(['resources/js/echo.ts'])),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                (function () {
                    function applyDuration(el) {
                        if (el._kkDurationSet) return;
                        el._kkDurationSet = true;

                        const xData = el.getAttribute('x-data') ?? '';
                        const match = xData.match(/"duration"\s*:\s*"?([^",}\s]+)"?/);
                        const raw   = match ? match[1] : '6000';

                        if (raw === 'persistent') {
                            el.classList.add('kk-no-persistent');
                        } else {
                            el.style.setProperty('--kk-no-duration', (parseInt(raw, 10) / 1000) + 's');
                        }
                    }

                    // Observe the entire body for new .fi-no-notification elements
                    new MutationObserver(function (mutations) {
                        for (const { addedNodes } of mutations) {
                            for (const node of addedNodes) {
                                if (node.nodeType !== 1) continue;
                                if (node.classList?.contains('fi-no-notification')) applyDuration(node);
                                node.querySelectorAll?.('.fi-no-notification').forEach(applyDuration);
                            }
                        }
                    }).observe(document.body, { childList: true, subtree: true });

                    // Handle notifications already in the DOM (session flash)
                    document.querySelectorAll('.fi-no-notification').forEach(applyDuration);
                })();
                </script>
                HTML),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.dashboard.auth.social'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
                fn (): View => view('filament.dashboard.auth.social'),
            )
            ->font(
                'area-normal',
                url: 'https://use.typekit.net/ztn2kjh.css',
                provider: LocalFontProvider::class,
            )
            ->colors([
                'primary' => Color::hex('#004e98'),
                'info' => Color::hex('#3a6ea5'),
                'warning' => Color::hex('#ff6700'),
                'gray' => Color::hex('#c0c0c0'),
            ])
            ->discoverResources(in: app_path('Filament/Dashboard/Resources'), for: 'App\Filament\Dashboard\Resources')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages'), for: 'App\Filament\Dashboard\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Dashboard/Widgets'), for: 'App\Filament\Dashboard\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn () => view('components.filament.contact-nav-item'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('components.filament.profile-nav-item'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(app(Vite::class)(['resources/js/app.js'])),
            );
    }
}
