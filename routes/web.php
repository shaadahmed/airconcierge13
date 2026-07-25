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
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Chronology;
use App\Models\PropertyPayment;
use App\Models\User;
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
        Route::get('dashboard', DashboardController::class)
            ->middleware('can:viewAny,'.Booking::class)
            ->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])
            ->middleware('can:viewAny,'.Booking::class)
            ->name('dashboard.stats');
        Route::get('dashboard/revenue-chart', [DashboardController::class, 'revenueChart'])
            ->middleware('can:viewAny,'.Booking::class)
            ->name('dashboard.revenue-chart');
        Route::get('dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
        Route::get('dashboard/owners/{owner}/statement', [DashboardController::class, 'ownerStatement'])
            ->middleware('can:viewAny,'.Booking::class)
            ->name('dashboard.owner-statement');

        Route::middleware('can:accessSuperAdminArea,'.User::class)->group(function (): void {
            Route::get('failed-jobs', [FailedJobController::class, 'index'])->name('failed-jobs.index');
            Route::post('failed-jobs/retry-all', [FailedJobController::class, 'retryAll'])->name('failed-jobs.retry-all');
            Route::post('failed-jobs/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
            Route::delete('failed-jobs/{uuid}', [FailedJobController::class, 'destroy'])->name('failed-jobs.destroy');
        });

        Route::middleware('can:viewAny,'.Booking::class)->group(function (): void {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/tot', [ReportController::class, 'tot'])->name('reports.tot');
            Route::get('reports/metrics', [ReportController::class, 'metrics'])->name('reports.metrics');
        });

        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->middleware('can:update,booking')
            ->name('bookings.cancel');

        Route::get('payments', [PaymentController::class, 'index'])
            ->middleware('can:viewAny,'.BookingPayment::class)
            ->name('payments.index');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])
            ->middleware('can:delete,payment')
            ->name('payments.destroy');

        Route::get('property-payments', [PaymentController::class, 'propertyIndex'])
            ->middleware('can:viewAny,'.PropertyPayment::class)
            ->name('property-payments.index');
        Route::post('property-payments', [PaymentController::class, 'propertyStore'])->name('property-payments.store');
        Route::delete('property-payments/{propertyPayment}', [PaymentController::class, 'propertyDestroy'])
            ->middleware('can:delete,propertyPayment')
            ->name('property-payments.destroy');

        Route::middleware('can:viewAny,'.Booking::class)->group(function (): void {
            Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');
            Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
            Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('imports/emails', [ImportController::class, 'importedEmails'])->name('imports.emails.index');
        });

        Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');

        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::put('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');

        Route::post('imports/owners', [ImportController::class, 'importOwners'])->name('imports.owners');
        Route::post('imports/properties', [ImportController::class, 'importProperties'])->name('imports.properties');
        Route::post('imports/emails', [ImportController::class, 'storeImportedEmail'])->name('imports.emails.store');

        Route::get('chronologies', [ChronologyController::class, 'index'])->name('chronologies.index');
        Route::get('chronologies/create', [ChronologyController::class, 'create'])->name('chronologies.create');
        Route::post('chronologies', [ChronologyController::class, 'store'])->name('chronologies.store');
        Route::get('chronologies/templates', [ChronologyController::class, 'templates'])
            ->middleware('can:viewAny,'.Chronology::class)
            ->name('chronologies.templates');
        Route::get('chronologies/documents', [ChronologyController::class, 'documents'])
            ->middleware('can:viewAny,'.Chronology::class)
            ->name('chronologies.documents');
        Route::get('chronologies/check-name', [ChronologyController::class, 'checkName'])
            ->middleware('can:viewAny,'.Chronology::class)
            ->name('chronologies.check-name');
        Route::get('chronologies/{chronology}', [ChronologyController::class, 'show'])->name('chronologies.show');
        Route::get('chronologies/{chronology}/edit', [ChronologyController::class, 'edit'])->name('chronologies.edit');
        Route::put('chronologies/{chronology}', [ChronologyController::class, 'update'])->name('chronologies.update');
        Route::delete('chronologies/{chronology}', [ChronologyController::class, 'destroy'])->name('chronologies.destroy');
        Route::post('chronologies/{chronology}/copy', [ChronologyController::class, 'copy'])
            ->middleware('can:create,'.Chronology::class)
            ->name('chronologies.copy');
        Route::post('chronologies/{chronology}/orders', [ChronologyController::class, 'storeOrder'])->name('chronologies.orders.store');
        Route::put('chronologies/{chronology}/orders/{order}', [ChronologyController::class, 'updateOrder'])->name('chronologies.orders.update');
        Route::delete('chronologies/{chronology}/orders/{order}', [ChronologyController::class, 'destroyOrder'])
            ->middleware('can:update,chronology')
            ->name('chronologies.orders.destroy');
        Route::get('chronologies/{chronology}/owners', [ChronologyController::class, 'previewOwners'])
            ->middleware('can:view,chronology')
            ->name('chronologies.owners');
        Route::post('chronologies/{chronology}/owner-emails', [ChronologyController::class, 'storeOwnerEmails'])->name('chronologies.owner-emails.store');

        Route::get('send-emails/create', [EmailController::class, 'create'])
            ->middleware('can:viewAny,'.Chronology::class)
            ->name('send-emails.create');
        Route::post('send-emails', [EmailController::class, 'store'])->name('send-emails.store');

        Route::middleware('can:viewAny,'.Chronology::class)->group(function (): void {
            Route::get('zoho', [ZohoSignController::class, 'index'])->name('zoho.index');
            Route::get('zoho/download/{helloSignDetail}', [ZohoSignController::class, 'download'])->name('zoho.download');
        });

        Route::middleware(['owner.terms', 'owner.active'])->group(function (): void {
            Route::get('owner-statements', [OwnerStatementController::class, 'index'])
                ->middleware('can:viewOwnerStatements,'.User::class)
                ->name('owner-statements.index');
            Route::get('owner-statements/report', [OwnerStatementController::class, 'report'])
                ->name('owner-statements.report');
            Route::get('owner-statements/export', [OwnerStatementController::class, 'export'])
                ->name('owner-statements.export');

            Route::middleware('can:viewOwnerTerms,'.User::class)->group(function (): void {
                Route::get('terms', [TermsController::class, 'show'])->name('terms.show');
                Route::post('terms/agree', [TermsController::class, 'agree'])->name('terms.agree');
                Route::post('terms/disagree', [TermsController::class, 'disagree'])->name('terms.disagree');
            });
        });
    });
