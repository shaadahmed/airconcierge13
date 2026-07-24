<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Dropbox\DropboxFormService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessDropboxCsvJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  'csv'|'sync-db'|'status-update'  $action
     */
    public function __construct(public string $action = 'csv') {}

    public function handle(DropboxFormService $dropboxFormService): void
    {
        Log::info('ProcessDropboxCsvJob running.', $this->jobLogContext([
            'action' => $this->action,
        ]));

        match ($this->action) {
            'sync-db' => $dropboxFormService->syncDatabase(),
            'status-update' => $dropboxFormService->updateStatuses(),
            default => $dropboxFormService->processCsv(),
        };
    }
}
