<?php

namespace App\Providers;

use App\Models\BookingPayment;
use App\Models\Country;
use App\Models\Dashboard;
use App\Models\DocumentUpload;
use App\Models\Guest;
use App\Models\ImportedEmail;
use App\Models\ManagementType;
use App\Models\Manager;
use App\Models\Owner;
use App\Models\Platform;
use App\Models\PropertyPayment;
use App\Models\Region;
use App\Models\Resource;
use App\Models\RevenueSetting;
use App\Models\Report;
use App\Models\PaymentType;
use App\Models\User;
use App\Policies\CountryPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ImportPolicy;
use App\Policies\GuestPolicy;
use App\Policies\ManagementTypePolicy;
use App\Policies\ManagerPolicy;
use App\Policies\OwnerPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PaymentTypePolicy;
use App\Policies\PlatformPolicy;
use App\Policies\RegionPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\RevenueSettingPolicy;
use App\Policies\UserPolicy;
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
        Gate::policy(Guest::class, GuestPolicy::class);
        Gate::policy(Owner::class, OwnerPolicy::class);
        Gate::policy(Region::class, RegionPolicy::class);
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(Manager::class, ManagerPolicy::class);
        Gate::policy(Platform::class, PlatformPolicy::class);
        Gate::policy(PaymentType::class, PaymentTypePolicy::class);
        Gate::policy(ManagementType::class, ManagementTypePolicy::class);
        Gate::policy(Resource::class, ResourcePolicy::class);
        Gate::policy(RevenueSetting::class, RevenueSettingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
