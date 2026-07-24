<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Chronology\ChronologyService;
use App\Services\Zoho\ZohoSignService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Process a single Zoho Sign request, or poll completions when no detail id is set.
 */
class ProcessSignatureRequestJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    public function __construct(public ?int $helloSignDetailId = null) {}

    public function handle(ChronologyService $chronologyService, ZohoSignService $zohoSignService): void
    {
        if ($this->helloSignDetailId === null) {
            Log::info('ProcessSignatureRequestJob polling Zoho completions.', $this->jobLogContext());
            $chronologyService->pollZohoCompletions();

            return;
        }

        Log::info('ProcessSignatureRequestJob processing signature detail.', $this->jobLogContext([
            'entity_id' => $this->helloSignDetailId,
        ]));

        $zohoSignService->processCompletedRequest($this->helloSignDetailId);
    }
}
