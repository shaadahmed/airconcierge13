<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledAlertJob;
use Illuminate\Console\Command;

class OwnerBlockAbandonmentsResolveCommand extends Command
{
    protected $signature = 'owner-block-abandonments:resolve';

    protected $description = 'Queue owner-block abandonment resolution (domain body follow-up).';

    public function handle(): int
    {
        RunScheduledAlertJob::dispatch('owner-block-abandonments');
        $this->info('Queued RunScheduledAlertJob for owner-block-abandonments.');

        return self::SUCCESS;
    }
}
