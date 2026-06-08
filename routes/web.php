<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.send');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');
