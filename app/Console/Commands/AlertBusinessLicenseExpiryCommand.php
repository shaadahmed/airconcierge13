<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertBusinessLicenseExpiryCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:business-license-expiry';

    protected $description = 'Queue scheduled alert: business-license-expiry.';

    public function handle(): int
    {
        return $this->dispatchAlert('business-license-expiry');
    }
}
