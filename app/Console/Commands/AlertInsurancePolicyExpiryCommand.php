<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertInsurancePolicyExpiryCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:insurance-policy-expiry';

    protected $description = 'Queue scheduled alert: insurance-policy-expiry.';

    public function handle(): int
    {
        return $this->dispatchAlert('insurance-policy-expiry');
    }
}
