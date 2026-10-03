<?php

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
        Route::put('dashboard/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');

        Route::apiResource('users', UserController::class)->except('show');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
