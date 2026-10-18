<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\Region;
use App\Models\Subregion;
use App\Models\User;
use App\Policies\CountryPolicy;
use App\Policies\RegionPolicy;
use App\Policies\SubregionPolicy;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(Region::class, RegionPolicy::class);
        Gate::policy(Subregion::class, SubregionPolicy::class);
    }
}
