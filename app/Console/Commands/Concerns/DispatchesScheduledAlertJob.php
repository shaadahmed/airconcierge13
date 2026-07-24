<?php

namespace App\Console\Commands\Concerns;

use App\Jobs\RunScheduledAlertJob;

trait DispatchesScheduledAlertJob
{
    protected function dispatchAlert(string $alertKey): int
    {
        RunScheduledAlertJob::dispatch($alertKey);
        $this->components->info("Queued RunScheduledAlertJob for {$alertKey}.");

        return self::SUCCESS;
    }
}
