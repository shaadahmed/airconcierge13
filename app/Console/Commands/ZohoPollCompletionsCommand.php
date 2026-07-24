<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSignatureRequestJob;
use Illuminate\Console\Command;

class ZohoPollCompletionsCommand extends Command
{
    protected $signature = 'zoho:poll-completions';

    protected $description = 'Poll Zoho Sign for completed signature requests linked to chronology.';

    public function handle(): int
    {
        ProcessSignatureRequestJob::dispatch(null);
        $this->info('Queued ProcessSignatureRequestJob for Zoho completion polling.');

        return self::SUCCESS;
    }
}
