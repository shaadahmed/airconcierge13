<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertBookingMonthDifferenceCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:booking-month-difference';

    protected $description = 'Queue scheduled alert: booking-month-difference.';

    public function handle(): int
    {
        return $this->dispatchAlert('booking-month-difference');
    }
}
