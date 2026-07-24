<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertSecurityDepositCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:security-deposit';

    protected $description = 'Queue scheduled alert: security-deposit.';

    public function handle(): int
    {
        return $this->dispatchAlert('security-deposit');
    }
}
