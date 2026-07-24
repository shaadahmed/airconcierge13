<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Images\ImageCompressionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CompressImagesBatchJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    public function __construct(public int $limit = 100) {}

    public function handle(ImageCompressionService $compressionService): void
    {
        Log::info('CompressImagesBatchJob starting.', $this->jobLogContext([
            'limit' => $this->limit,
        ]));

        $processed = $compressionService->compressBatch($this->limit);

        Log::info('CompressImagesBatchJob finished.', $this->jobLogContext([
            'processed' => $processed,
        ]));
    }
}
