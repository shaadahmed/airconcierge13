<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Alerts\AlertDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Async wrapper for scheduled alert checks.
 *
 * Domain bodies may still be stubs — follow-up cards for full legacy parity.
 */
class RunScheduledAlertJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    public function __construct(public string $alertKey) {}

    public function handle(AlertDispatchService $alertDispatchService): void
    {
        Log::info('RunScheduledAlertJob starting.', $this->jobLogContext([
            'alert_key' => $this->alertKey,
        ]));

        $alertDispatchService->run($this->alertKey);
    }
}
