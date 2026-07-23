<?php

namespace App\Console\Commands;

use App\Services\Chronology\ChronologyService;
use Illuminate\Console\Command;

class ChronologyProcessSendsCommand extends Command
{
    protected $signature = 'chronology:process-sends';

    protected $description = 'Process due chronology drip emails and Zoho signature steps.';

    public function handle(ChronologyService $chronologyService): int
    {
        $sent = $chronologyService->processDueSends();
        $this->info("Processed {$sent} chronology send(s).");

        return self::SUCCESS;
    }
}
