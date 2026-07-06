<?php

use App\Http\Controllers\Shop\AnalyticsController;
use App\Http\Controllers\Shop\CarController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\ReminderController;
use App\Http\Controllers\Shop\VisitController;
use App\Http\Middleware\EnsureShopUser;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureShopUser::class])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Generous limits — a busy counter taps fast; these only stop abuse
    Route::get('cars/search', [CarController::class, 'search'])->middleware('throttle:60,1')->name('cars.search');
    Route::get('cars/{car}', [CarController::class, 'show'])->name('cars.show');

    Route::get('visits/new', [VisitController::class, 'create'])->name('visits.create');
    Route::post('visits', [VisitController::class, 'store'])->middleware('throttle:30,1')->name('visits.store');
    Route::delete('visits/{visit}', [VisitController::class, 'destroy'])->middleware('throttle:30,1')->name('visits.destroy');

    Route::get('reminders', [ReminderController::class, 'index'])->name('reminders');
    Route::post('reminders/{reminder}/contacted', [ReminderController::class, 'toggleContacted'])->middleware('throttle:60,1')->name('reminders.contact');

    Route::get('analytics', AnalyticsController::class)->name('analytics');
});
