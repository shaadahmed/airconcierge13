<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertPasswordExpiryCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:password-expiry';

    protected $description = 'Queue scheduled alert: password-expiry.';

    public function handle(): int
    {
        return $this->dispatchAlert('password-expiry');
    }
}
