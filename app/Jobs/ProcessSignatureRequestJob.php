<?php

namespace App\Jobs;

use App\Services\Chronology\ChronologyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Phase 2.2 stub — retries/monitoring hardened in Phase 4.
 */
class ProcessSignatureRequestJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?int $helloSignDetailId = null) {}

    public function handle(ChronologyService $chronologyService): void
    {
        $chronologyService->pollZohoCompletions();
    }
}
