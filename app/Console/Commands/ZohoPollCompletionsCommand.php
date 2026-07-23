<?php

namespace App\Console\Commands;

use App\Services\Chronology\ChronologyService;
use Illuminate\Console\Command;

class ZohoPollCompletionsCommand extends Command
{
    protected $signature = 'zoho:poll-completions';

    protected $description = 'Poll Zoho Sign for completed signature requests linked to chronology.';

    public function handle(ChronologyService $chronologyService): int
    {
        $completed = $chronologyService->pollZohoCompletions();
        $this->info("Marked {$completed} Zoho signature request(s) complete.");

        return self::SUCCESS;
    }
}
