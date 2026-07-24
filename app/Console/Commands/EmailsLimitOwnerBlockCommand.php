<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledAlertJob;
use Illuminate\Console\Command;

class EmailsLimitOwnerBlockCommand extends Command
{
    protected $signature = 'emails:limit-owner-block';

    protected $description = 'Queue owner-block email limit job (domain body follow-up).';

    public function handle(): int
    {
        RunScheduledAlertJob::dispatch('emails-limit-owner-block');
        $this->info('Queued RunScheduledAlertJob for emails-limit-owner-block.');

        return self::SUCCESS;
    }
}
