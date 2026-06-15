<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContractPdfController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomReviewController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/koten', [RoomController::class, 'index'])->name('rooms.index');
// Stateless JSON lookup: skipping session/cookies saves remote DB roundtrips
// (sessions and cache live in the database) — suggestions should be snappy.
Route::get('/koten/suggesties', [RoomController::class, 'suggestions'])
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        PreventRequestForgery::class,
    ])
    ->middleware('throttle:60,1')
    ->name('rooms.suggestions');
Route::get('/koten/{room}', [RoomController::class, 'show'])->name('rooms.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

// Room score survey: public token link, created when a rental ends
// (see ReviewInvitation). The token is the only access control.
Route::get('/beoordeling/{invitation:token}', [RoomReviewController::class, 'create'])
    ->middleware('throttle:30,1')
    ->name('reviews.create');
Route::post('/beoordeling/{invitation:token}', [RoomReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('reviews.store');

// Social login (Google)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Contract PDF (authenticated, toegankelijk voor huurder + verhuurder)
Route::middleware('auth')->get('/contracten/{document}/pdf', ContractPdfController::class)
    ->name('contracts.pdf');

// One-time role choice after social sign-up
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/role', [OnboardingController::class, 'showRole'])->name('onboarding.role');
    Route::post('/onboarding/role', [OnboardingController::class, 'storeRole'])->name('onboarding.role.store');
});

// Policies
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/cookies', 'cookies')->name('cookies');
Route::view('/algemene-voorwaarden', 'algemene-voorwaarden')->name('algemene-voorwaarden');
Route::view('/gegevens-verwijderen', 'gegevens-verwijderen')->name('gegevens-verwijderen');


Route::get('/mail-preview', function () {
    return new \App\Mail\SupportContactMail(
        senderName: 'Test Gebruiker',
        senderEmail: 'test@example.com',
        subjectLine: 'Test onderwerp',
        body: 'Dit is een testbericht.',
    );
});

Route::get('/mail-preview/notification', function () {
    return view('mailing.notification', [
        'greeting'   => 'Hallo Jan!',
        'lines'      => [
            'Je aanvraag voor het kot aan de Kerkstraat 12 is goedgekeurd.',
            'De verhuurder heeft je uitgenodigd voor een bezichtiging.',
        ],
        'actionText' => 'Bekijk je dashboard',
        'actionUrl'  => url('/dashboard'),
        'type'       => 'success',
    ]);
});

Route::get('/mail-preview/auth', function () {
    return view('mailing.auth', [
        'heading'    => 'Bevestig je e-mailadres',
        'lines'      => ['Klik op de knop hieronder om je e-mailadres te bevestigen en je account te activeren.'],
        'actionText' => 'E-mailadres bevestigen',
        'actionUrl'  => url('/verify-email/example-token'),
        'expiresIn'  => '60 minuten',
    ]);
});
