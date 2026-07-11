<?php

use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\AnalyticsController;
use App\Http\Controllers\Shop\AnnouncementController;
use App\Http\Controllers\Shop\CarController;
use App\Http\Controllers\Shop\ClientController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\EntryController;
use App\Http\Controllers\Shop\InboxController;
use App\Http\Controllers\Shop\RegistrationController;
use App\Http\Controllers\Shop\ReminderController;
use App\Http\Controllers\Shop\ServicePriceController;
use App\Http\Controllers\Shop\VisitController;
use App\Http\Middleware\EnsureShopUser;
use App\Http\Middleware\SetShopLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SetShopLocale::class, EnsureShopUser::class])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // The new-visit landing (instant search + recents + new-customer shortcut)
    Route::get('entry', [EntryController::class, 'index'])->name('entry');

    // The clients directory (all registered customers/cars, recency-sorted)
    Route::get('clients', [ClientController::class, 'index'])->name('clients');

    // Generous limits — a busy counter taps fast; these only stop abuse
    Route::get('cars/search', [CarController::class, 'search'])->middleware('throttle:60,1')->name('cars.search');
    Route::get('cars/{car}/edit', [CarController::class, 'edit'])->name('cars.edit');
    Route::put('cars/{car}', [CarController::class, 'update'])->middleware('throttle:30,1')->name('cars.update');
    Route::get('cars/{car}', [CarController::class, 'show'])->name('cars.show');

    Route::get('visits/new', [VisitController::class, 'create'])->name('visits.create');
    Route::post('visits', [VisitController::class, 'store'])->middleware('throttle:30,1')->name('visits.store');
    Route::get('visits/{visit}/edit', [VisitController::class, 'edit'])->name('visits.edit');
    Route::put('visits/{visit}', [VisitController::class, 'update'])->middleware('throttle:30,1')->name('visits.update');
    Route::delete('visits/{visit}', [VisitController::class, 'destroy'])->middleware('throttle:30,1')->name('visits.destroy');

    Route::get('reminders', [ReminderController::class, 'index'])->name('reminders');
    Route::post('reminders/{reminder}/contacted', [ReminderController::class, 'toggleContacted'])->middleware('throttle:60,1')->name('reminders.contact');

    Route::get('analytics', AnalyticsController::class)->name('analytics');

    Route::post('announcements/{announcement}/dismiss', [AnnouncementController::class, 'dismiss'])->middleware('throttle:30,1')->name('announcements.dismiss');

    Route::get('messages', [InboxController::class, 'index'])->name('messages');
    Route::post('suggestions', [InboxController::class, 'storeSuggestion'])->middleware('throttle:20,1')->name('suggestions.store');

    Route::get('service-prices', [ServicePriceController::class, 'edit'])->name('service-prices');
    Route::put('service-prices', [ServicePriceController::class, 'update'])->middleware('throttle:20,1')->name('service-prices.update');

    // QR self-registration: the QR/link to show + the pending queue to accept/reject
    Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations');
    Route::post('registrations/{pendingRegistration}/accept', [RegistrationController::class, 'accept'])->middleware('throttle:60,1')->name('registrations.accept');
    Route::delete('registrations/{pendingRegistration}', [RegistrationController::class, 'reject'])->middleware('throttle:60,1')->name('registrations.reject');

    Route::get('account', [AccountController::class, 'edit'])->name('account');
    Route::put('account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:10,1')->name('account.password');
    Route::put('account/settings', [AccountController::class, 'updateSettings'])->middleware('throttle:20,1')->name('account.settings');
    Route::post('account/avatar', [AccountController::class, 'updateAvatar'])->middleware('throttle:20,1')->name('account.avatar');
    Route::delete('account/avatar', [AccountController::class, 'deleteAvatar'])->middleware('throttle:20,1')->name('account.avatar.delete');
});
