<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Import\ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessImportedEmailJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array{source?: string, subject?: string, body?: string, from_email?: string}  $attributes
     */
    public function __construct(public array $attributes) {}

    public function handle(ImportService $importService): void
    {
        Log::info('ProcessImportedEmailJob storing imported email.', $this->jobLogContext([
            'source' => $this->attributes['source'] ?? null,
        ]));

        $importService->storeImportedEmail($this->attributes);
    }
}
