<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/koten', [RoomController::class, 'index'])->name('rooms.index');
// Stateless JSON-lookup: sessie/cookies overslaan scheelt remote DB-roundtrips
// (sessies en cache leven in de database) — suggesties moeten snappy zijn.
Route::get('/koten/suggesties', [RoomController::class, 'suggestions'])
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
    ])
    ->middleware('throttle:60,1')
    ->name('rooms.suggestions');
Route::get('/koten/{room}', [RoomController::class, 'show'])->name('rooms.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.send');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

// Social login (Google)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// One-time role choice after social sign-up
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/role', [OnboardingController::class, 'showRole'])->name('onboarding.role');
    Route::post('/onboarding/role', [OnboardingController::class, 'storeRole'])->name('onboarding.role.store');
});

// Policies
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/cookies', 'cookies')->name('cookies');
Route::view('/algemene-voorwaarden', 'algemene-voorwaarden')->name('algemene-voorwaarden');
