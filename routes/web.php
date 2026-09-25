<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ChronologyController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\CockpitController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CronSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\HostawayLogController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\ManagementTypeController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\OutboundEmailLogController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\OwnerStatementController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\PropertyAuditController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\RevenueSettingsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TermsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\ZohoSignController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ChronologyMailOpenController;
use App\Http\Controllers\Webhooks\HostawayWebhookController;
use App\Models\BookingPayment;
use App\Models\Chronology;
use App\Models\Dashboard;
use App\Models\ImportedEmail;
use App\Models\PropertyPayment;
use App\Models\Report;
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
        Route::get('navigation', NavigationController::class)->name('navigation');

        Route::get('dashboard', DashboardController::class)
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.stats');
        Route::get('dashboard/revenue-chart', [DashboardController::class, 'revenueChart'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.revenue-chart');
        Route::get('dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
        Route::put('dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
        Route::get('dashboard/accounting', [DashboardController::class, 'accounting'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.accounting');
        Route::get('dashboard/ownerblock/all', [DashboardController::class, 'ownerBlocks'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.ownerblocks');
        Route::get('dashboard/ownercalendarview', [DashboardController::class, 'ownerCalendarView'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.ownercalendarview');
        Route::get('dashboard/threshold-properties', [DashboardController::class, 'thresholdProperties'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.threshold-properties');
        Route::get('dashboard/cashflow', [DashboardController::class, 'cashflow'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.cashflow');
        Route::get('dashboard/ownerstatements', [DashboardController::class, 'ownerStatements'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.ownerstatements');
        Route::get('dashboard/owners/{owner}/statement', [DashboardController::class, 'ownerStatement'])
            ->middleware('can:viewAny,'.Dashboard::class)
            ->name('dashboard.owner-statement');

        Route::middleware('can:accessSuperAdminArea,'.User::class)->group(function (): void {
            Route::get('failed-jobs', [FailedJobController::class, 'index'])->name('failed-jobs.index');
            Route::post('failed-jobs/retry-all', [FailedJobController::class, 'retryAll'])->name('failed-jobs.retry-all');
            Route::post('failed-jobs/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
            Route::delete('failed-jobs/{uuid}', [FailedJobController::class, 'destroy'])->name('failed-jobs.destroy');
        });

        Route::middleware('can:viewAny,'.Report::class)->group(function (): void {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/tot', [ReportController::class, 'tot'])->name('reports.tot');
            Route::get('reports/metrics', [ReportController::class, 'metrics'])->name('reports.metrics');
            Route::get('reports/properties', [ReportController::class, 'propertiesReport'])->name('reports.properties');
            Route::get('reports/nightspayouts', [ReportController::class, 'nightsPayouts'])->name('reports.nightspayouts');
            Route::get('reports/bookingstay', [ReportController::class, 'bookingStay'])->name('reports.bookingstay');
            Route::get('reports/regionsincome', [ReportController::class, 'regionsIncome'])->name('reports.regionsincome');
            Route::get('reports/totalincome', [ReportController::class, 'totalIncome'])->name('reports.totalincome');
            Route::get('reports/guestlocation', [ReportController::class, 'guestLocation'])->name('reports.guestlocation');
            Route::get('reports/properties/threshold', [ReportController::class, 'threshold'])->name('reports.properties.threshold');
            Route::get('reports/properties/closing-history', [ReportController::class, 'closingHistory'])->name('reports.properties.closing-history');
            Route::get('reports/owner-block-abandonment', [ReportController::class, 'ownerBlockAbandonment'])->name('reports.owner-block-abandonment');
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

        Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');

        Route::get('guests/export-emails', [GuestController::class, 'exportEmails'])->name('guests.export-emails');
        Route::apiResource('guests', GuestController::class)->except('show');
        Route::apiResource('owners', OwnerController::class)->except('show');
        Route::get('regions/{region}/subregions', [RegionController::class, 'subregions'])->name('regions.subregions');
        Route::apiResource('regions', RegionController::class)->except('show');
        Route::get('countries', [CountryController::class, 'index'])->name('countries.index');
        Route::post('countries', [CountryController::class, 'store'])->name('countries.store');
        Route::put('countries/{country}', [CountryController::class, 'update'])->name('countries.update');
        Route::delete('countries/{country}', [CountryController::class, 'destroy'])->name('countries.destroy');
        Route::post('countries/{country}/states', [CountryController::class, 'storeState'])->name('countries.states.store');
        Route::put('countries/{country}/states/{state}', [CountryController::class, 'updateState'])->name('countries.states.update');
        Route::delete('countries/{country}/states/{state}', [CountryController::class, 'destroyState'])->name('countries.states.destroy');
        Route::apiResource('managers', ManagerController::class)->except('show');
        Route::apiResource('vendors', VendorController::class)->only(['index', 'store', 'update']);
        Route::apiResource('platforms', PlatformController::class)->except('show');
        Route::apiResource('payment_types', PaymentTypeController::class)->except('show');
        Route::apiResource('management_types', ManagementTypeController::class)->except('show');
        Route::apiResource('resources', ResourceController::class)->except('show');
        Route::get('cms', [CmsController::class, 'index'])->name('cms.index');
        Route::put('cms/{pageId}', [CmsController::class, 'update'])->name('cms.update');
        Route::apiResource('users', UserController::class)->except('show');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('hostawaylogs', [HostawayLogController::class, 'index'])->name('hostawaylogs.index');
        Route::get('airbnbemails', [ImportController::class, 'importedEmails'])->name('airbnbemails.index');
        Route::get('outbound-email-logs', [OutboundEmailLogController::class, 'index'])->name('outbound-email-logs.index');
        Route::get('settings/cockpit', [CockpitController::class, 'index'])->name('settings.cockpit');
        Route::get('settings/system', [SystemSettingsController::class, 'index'])->name('settings.system');
        Route::get('settings/cron', [CronSettingsController::class, 'index'])->name('settings.cron');
        Route::get('settings/revenue', [RevenueSettingsController::class, 'index'])->name('settings.revenue');
        Route::post('settings/revenue', [RevenueSettingsController::class, 'store'])->name('settings.revenue.store');
        Route::put('settings/revenue/{revenueSetting}', [RevenueSettingsController::class, 'update'])->name('settings.revenue.update');
        Route::get('export/guestemailslist', [GuestController::class, 'exportEmails'])->name('export.guestemailslist');
        Route::get('properties/audit/list', [PropertyAuditController::class, 'index'])->name('properties.audit.list');
        Route::post('properties/audit', [PropertyAuditController::class, 'store'])->name('properties.audit.store');

        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::put('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::middleware('can:viewAny,'.ImportedEmail::class)->group(function (): void {
            Route::get('imports/emails', [ImportController::class, 'importedEmails'])->name('imports.emails.index');
        });
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
