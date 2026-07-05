<?php

use App\Http\Controllers\Shop\ShopScreensController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopScreensController::class, 'dashboard'])->name('dashboard');
    Route::get('visits/new', [ShopScreensController::class, 'createVisit'])->name('visits.create');
    Route::get('cars/demo', [ShopScreensController::class, 'showCar'])->name('cars.show');
    Route::get('reminders', [ShopScreensController::class, 'reminders'])->name('reminders');
    Route::get('analytics', [ShopScreensController::class, 'analytics'])->name('analytics');
});
