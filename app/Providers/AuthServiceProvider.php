<?php

namespace App\Providers;

use App\Models\BookingPayment;
use App\Models\Dashboard;
use App\Models\DocumentUpload;
use App\Models\ImportedEmail;
use App\Models\PropertyPayment;
use App\Models\Report;
use App\Policies\DashboardPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ImportPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    public function boot(): void
    {
        Gate::policy(BookingPayment::class, PaymentPolicy::class);
        Gate::policy(PropertyPayment::class, PaymentPolicy::class);
        Gate::policy(DocumentUpload::class, DocumentPolicy::class);
        Gate::policy(ImportedEmail::class, ImportPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(Dashboard::class, DashboardPolicy::class);
    }
}
