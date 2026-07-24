<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertPropertyPermitExpiryCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:property-permit-expiry';

    protected $description = 'Queue scheduled alert: property-permit-expiry.';

    public function handle(): int
    {
        return $this->dispatchAlert('property-permit-expiry');
    }
}
