<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledAlertJob;
use App\Services\Alerts\AlertDispatchService;
use Illuminate\Console\Command;

class AlertBookingConflictCommand extends Command
{
    protected $signature = 'alert:booking-conflict {--sync : Run inline instead of queueing}';

    protected $description = 'Detect overlapping non-cancelled bookings on the same property.';

    public function handle(AlertDispatchService $alerts): int
    {
        if ($this->option('sync')) {
            $conflicts = $alerts->detectBookingConflicts();
            $this->info("Found {$conflicts} booking conflict pair(s).");

            return self::SUCCESS;
        }

        RunScheduledAlertJob::dispatch('booking-conflict');
        $this->info('Queued RunScheduledAlertJob for booking-conflict.');

        return self::SUCCESS;
    }
}
