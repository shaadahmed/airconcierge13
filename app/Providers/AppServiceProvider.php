<?php

namespace App\Providers;

use App\Contracts\OwnerActiveAccessChecker;
use App\Contracts\OwnerTermsChecker;
use App\Services\Auth\StubOwnerActiveAccessChecker;
use App\Services\Auth\StubOwnerTermsChecker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OwnerTermsChecker::class, StubOwnerTermsChecker::class);
        $this->app->bind(OwnerActiveAccessChecker::class, StubOwnerActiveAccessChecker::class);
    }

    public function boot(): void
    {
        //
    }
}
