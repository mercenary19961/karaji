<?php

use App\Http\Controllers\JoinController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// No public landing page in v1: guests go to login, users to their portal.
Route::get('/', function () {
    return Auth::check() ? redirect(Auth::user()->homeRoute()) : redirect()->route('login');
})->name('home');

// Guest-accessible language toggle for the auth pages (persists in session).
Route::get('locale/{locale}', LocaleController::class)->name('locale');

// Public QR self-registration (a shop's customers scan → register their car).
Route::get('join/{token}', [JoinController::class, 'show'])->name('join.show');
Route::post('join/{token}', [JoinController::class, 'store'])->middleware('throttle:10,1')->name('join.store');

Route::middleware(['auth'])->group(function () {
    // Legacy scaffold route — kept only so stray route('dashboard') links land
    // on the right portal instead of the starter-kit placeholder.
    Route::get('dashboard', function () {
        return redirect(Auth::user()->homeRoute());
    })->name('dashboard');
});

require __DIR__.'/shop.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
