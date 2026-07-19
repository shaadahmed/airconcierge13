<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OwnerStatementController;
use App\Http\Controllers\Admin\TermsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Webhooks\HostawayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
| Hostaway webhook — path preserved for cutover.
| Authenticated via Hostaway-native Basic Auth (ADR-008).
*/
Route::post('wh/hostaway/booking/created', HostawayWebhookController::class)
    ->name('webhooks.hostaway.booking.created');

Route::middleware(['auth', 'owner.terms', 'owner.active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('owner-statements', [OwnerStatementController::class, 'index'])
            ->name('owner-statements.index');

        Route::get('terms', [TermsController::class, 'show'])
            ->name('terms.show');

        // Domain routes (bookings, properties, reports, …) are added in later phases.
    });
