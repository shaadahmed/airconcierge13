<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertConsecutiveGuestBookingsCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:consecutive-guest-bookings';

    protected $description = 'Queue scheduled alert: consecutive-guest-bookings.';

    public function handle(): int
    {
        return $this->dispatchAlert('consecutive-guest-bookings');
    }
}
