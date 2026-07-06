<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ShopsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Middleware\EnsureAdminUser;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SetAdminLocale::class, EnsureAdminUser::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [ShopsController::class, 'index'])->name('shops.index');
    Route::get('shops/{shop}', [ShopsController::class, 'show'])->name('shops.show');

    Route::put('shops/{shop}/subscription', [SubscriptionController::class, 'update'])->middleware('throttle:30,1')->name('shops.subscription');
    Route::post('shops/{shop}/subscription/extend-trial', [SubscriptionController::class, 'extendTrial'])->middleware('throttle:30,1')->name('shops.subscription.extend');

    Route::post('shops/{shop}/impersonate', [ImpersonationController::class, 'store'])->middleware('throttle:10,1')->name('shops.impersonate');

    Route::post('activity-logs/{activityLog}/undo', [ActivityLogController::class, 'undo'])->middleware('throttle:30,1')->name('activity.undo');
});

// Outside the admin group on purpose: the user is a SHOP user mid-impersonation
Route::middleware(['auth', 'throttle:10,1'])->post('impersonation/leave', [ImpersonationController::class, 'destroy'])->name('impersonation.leave');
