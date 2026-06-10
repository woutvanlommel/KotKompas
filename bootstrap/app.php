<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // No generic "login" route (auth lives in Filament panels) — send guests
        // on web routes (e.g. /onboarding/role) to the dashboard login.
        $middleware->redirectGuestsTo(fn () => route('filament.dashboard.auth.login'));

        // SECURITY: Enable Sanctum stateful authentication for the broadcast
        // auth endpoint (/broadcasting/auth). This ensures the session cookie
        // issued by Filament login is accepted when the WebSocket client
        // authenticates private channels — no separate token needed, and
        // unauthenticated requests are rejected at the middleware level.
        $middleware->statefulApi();
    })
    ->booted(function () {
        // SECURITY: Rate limit the broadcast channel auth endpoint.
        // Caps authentication attempts at 30 per minute, keyed by user ID
        // for logged-in users or by IP address for unauthenticated requests.
        // This prevents attackers from brute-forcing conversation IDs to
        // gain access to private channels.
        RateLimiter::for('broadcasting', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
