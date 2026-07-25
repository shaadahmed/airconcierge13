<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Google Drive (`gdrive` disk) registration lands with masbug/flysystem-google-drive-ext
        // when Composer can install google/apiclient-services (ADR-014). Until then backups stay local.
    }
}
