<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Metrics\PropertyMetricsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecalculatePropertyMetricsJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    public function __construct(public bool $full = false)
    {
        $this->timeout = 300;
    }

    public function handle(PropertyMetricsService $metricsService): void
    {
        Log::info('RecalculatePropertyMetricsJob starting.', $this->jobLogContext([
            'full' => $this->full,
        ]));

        $count = $metricsService->recalculate($this->full);

        Log::info('RecalculatePropertyMetricsJob finished.', $this->jobLogContext([
            'rows_updated' => $count,
        ]));
    }
}
