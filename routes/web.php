<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\SubregionController;
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

        Route::apiResource('countries', CountryController::class)->except(['show']);
        Route::apiResource('countries.states', StateController::class)
            ->except(['index', 'show'])
            ->scoped();
        Route::apiResource('countries.states.cities', CityController::class)
            ->except(['index', 'show'])
            ->scoped();

        // Literal /regions/subregions before {region} binding.
        Route::get('regions/subregions', [SubregionController::class, 'index'])->name('regions.subregions.index');
        Route::post('regions/subregions', [SubregionController::class, 'store'])->name('regions.subregions.store');
        Route::put('regions/subregions/{subregion}', [SubregionController::class, 'update'])->name('regions.subregions.update');
        Route::delete('regions/subregions/{subregion}', [SubregionController::class, 'destroy'])->name('regions.subregions.destroy');

        Route::apiResource('regions', RegionController::class)->except(['show']);
    });
