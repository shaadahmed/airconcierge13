<?php

namespace App\Providers;

use App\Models\BookingPayment;
use App\Models\PropertyPayment;
use App\Policies\PaymentPolicy;
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
    }
}
