<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertRecurringPaymentsCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:recurring-payments';

    protected $description = 'Queue scheduled alert: recurring-payments.';

    public function handle(): int
    {
        return $this->dispatchAlert('recurring-payments');
    }
}
