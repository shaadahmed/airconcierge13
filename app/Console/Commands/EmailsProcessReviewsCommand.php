<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledAlertJob;
use Illuminate\Console\Command;

class EmailsProcessReviewsCommand extends Command
{
    protected $signature = 'emails:process-reviews';

    protected $description = 'Queue review-email processing (domain body follow-up).';

    public function handle(): int
    {
        RunScheduledAlertJob::dispatch('emails-process-reviews');
        $this->info('Queued RunScheduledAlertJob for emails-process-reviews.');

        return self::SUCCESS;
    }
}
