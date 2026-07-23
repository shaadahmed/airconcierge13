<?php

namespace App\Providers;

use App\Models\BookingPayment;
use App\Models\PropertyPayment;
use App\Policies\PaymentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(BookingPayment::class, PaymentPolicy::class);
        Gate::policy(PropertyPayment::class, PaymentPolicy::class);
    }
}
