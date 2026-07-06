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

    Route::put('shops/{shop}/subscription', [SubscriptionController::class, 'update'])->name('shops.subscription');
    Route::post('shops/{shop}/subscription/extend-trial', [SubscriptionController::class, 'extendTrial'])->name('shops.subscription.extend');

    Route::post('shops/{shop}/impersonate', [ImpersonationController::class, 'store'])->name('shops.impersonate');

    Route::post('activity-logs/{activityLog}/undo', [ActivityLogController::class, 'undo'])->name('activity.undo');
});

// Outside the admin group on purpose: the user is a SHOP user mid-impersonation
Route::middleware('auth')->post('impersonation/leave', [ImpersonationController::class, 'destroy'])->name('impersonation.leave');
