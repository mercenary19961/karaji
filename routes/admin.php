<?php

use App\Http\Controllers\Admin\AdminScreensController;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;

// TODO(schema v1): add an admin-role gate once roles exist — plain `auth` is
// demo-phase only.
Route::middleware(['auth', SetAdminLocale::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminScreensController::class, 'index'])->name('shops.index');
    Route::get('shops/demo', [AdminScreensController::class, 'show'])->name('shops.show');
});
