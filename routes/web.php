<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ChronologyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\OwnerStatementController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TermsController;
use App\Http\Controllers\Admin\ZohoSignController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ChronologyMailOpenController;
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

Route::get('chronology/mail/{mail}/opened', ChronologyMailOpenController::class)
    ->name('chronology.mail.opened');

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/revenue-chart', [DashboardController::class, 'revenueChart'])->name('dashboard.revenue-chart');
        Route::get('dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
        Route::get('dashboard/owners/{owner}/statement', [DashboardController::class, 'ownerStatement'])->name('dashboard.owner-statement');

        Route::get('failed-jobs', [FailedJobController::class, 'index'])->name('failed-jobs.index');
        Route::post('failed-jobs/retry-all', [FailedJobController::class, 'retryAll'])->name('failed-jobs.retry-all');
        Route::post('failed-jobs/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
        Route::delete('failed-jobs/{uuid}', [FailedJobController::class, 'destroy'])->name('failed-jobs.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/tot', [ReportController::class, 'tot'])->name('reports.tot');
        Route::get('reports/metrics', [ReportController::class, 'metrics'])->name('reports.metrics');

        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('property-payments', [PaymentController::class, 'propertyIndex'])->name('property-payments.index');
        Route::post('property-payments', [PaymentController::class, 'propertyStore'])->name('property-payments.store');
        Route::delete('property-payments/{propertyPayment}', [PaymentController::class, 'propertyDestroy'])->name('property-payments.destroy');

        Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::put('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::post('imports/owners', [ImportController::class, 'importOwners'])->name('imports.owners');
        Route::post('imports/properties', [ImportController::class, 'importProperties'])->name('imports.properties');
        Route::get('imports/emails', [ImportController::class, 'importedEmails'])->name('imports.emails.index');
        Route::post('imports/emails', [ImportController::class, 'storeImportedEmail'])->name('imports.emails.store');

        Route::get('chronologies', [ChronologyController::class, 'index'])->name('chronologies.index');
        Route::get('chronologies/create', [ChronologyController::class, 'create'])->name('chronologies.create');
        Route::post('chronologies', [ChronologyController::class, 'store'])->name('chronologies.store');
        Route::get('chronologies/templates', [ChronologyController::class, 'templates'])->name('chronologies.templates');
        Route::get('chronologies/documents', [ChronologyController::class, 'documents'])->name('chronologies.documents');
        Route::get('chronologies/check-name', [ChronologyController::class, 'checkName'])->name('chronologies.check-name');
        Route::get('chronologies/{chronology}', [ChronologyController::class, 'show'])->name('chronologies.show');
        Route::get('chronologies/{chronology}/edit', [ChronologyController::class, 'edit'])->name('chronologies.edit');
        Route::put('chronologies/{chronology}', [ChronologyController::class, 'update'])->name('chronologies.update');
        Route::delete('chronologies/{chronology}', [ChronologyController::class, 'destroy'])->name('chronologies.destroy');
        Route::post('chronologies/{chronology}/copy', [ChronologyController::class, 'copy'])->name('chronologies.copy');
        Route::post('chronologies/{chronology}/orders', [ChronologyController::class, 'storeOrder'])->name('chronologies.orders.store');
        Route::put('chronologies/{chronology}/orders/{order}', [ChronologyController::class, 'updateOrder'])->name('chronologies.orders.update');
        Route::delete('chronologies/{chronology}/orders/{order}', [ChronologyController::class, 'destroyOrder'])->name('chronologies.orders.destroy');
        Route::get('chronologies/{chronology}/owners', [ChronologyController::class, 'previewOwners'])->name('chronologies.owners');
        Route::post('chronologies/{chronology}/owner-emails', [ChronologyController::class, 'storeOwnerEmails'])->name('chronologies.owner-emails.store');

        Route::get('send-emails/create', [EmailController::class, 'create'])->name('send-emails.create');
        Route::post('send-emails', [EmailController::class, 'store'])->name('send-emails.store');

        Route::get('zoho', [ZohoSignController::class, 'index'])->name('zoho.index');
        Route::get('zoho/download/{helloSignDetail}', [ZohoSignController::class, 'download'])->name('zoho.download');

        Route::middleware(['owner.terms', 'owner.active'])->group(function (): void {
            Route::get('owner-statements', [OwnerStatementController::class, 'index'])
                ->name('owner-statements.index');

            Route::get('terms', [TermsController::class, 'show'])
                ->name('terms.show');
        });
    });
