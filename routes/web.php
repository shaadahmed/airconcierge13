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

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        // Shared authenticated admin shell — no owner-specific middleware.
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        // Owner boundary — Owner role flows only (ADR-009 / ADR-010).
        Route::middleware(['owner.terms', 'owner.active'])->group(function (): void {
            Route::get('owner-statements', [OwnerStatementController::class, 'index'])
                ->name('owner-statements.index');

            Route::get('terms', [TermsController::class, 'show'])
                ->name('terms.show');
        });
    });
