<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledAlertJob;
use Illuminate\Console\Command;

class OwnersPayoutCommand extends Command
{
    protected $signature = 'owners:payout';

    protected $description = 'Queue owners payout processing (domain body follow-up).';

    public function handle(): int
    {
        RunScheduledAlertJob::dispatch('owners-payout');
        $this->info('Queued RunScheduledAlertJob for owners-payout.');

        return self::SUCCESS;
    }
}
