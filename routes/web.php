<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\RedirectController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::view('/', 'home')->name('home');

Route::get('/qr', [QrController::class, 'show'])->name('qr');
Route::post('/qr/preview', [QrController::class, 'preview'])->middleware('throttle:120,1')->name('qr.preview');
Route::post('/qr/download', [QrController::class, 'download'])->middleware('throttle:30,1')->name('qr.download');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [LinkController::class, 'index'])->name('dashboard');
    Route::post('/links', [LinkController::class, 'store'])->middleware('throttle:30,1')->name('links.store');
    Route::get('/links/{link}/edit', [LinkController::class, 'edit'])->name('links.edit');
    Route::put('/links/{link}', [LinkController::class, 'update'])->name('links.update');
    Route::delete('/links/{link}', [LinkController::class, 'destroy'])->name('links.destroy');
});

// Must stay last: crqr.in/{code}. Runs without sessions/cookies so each click stays cheap.
Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9_-]{1,32}')
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('links.redirect');
