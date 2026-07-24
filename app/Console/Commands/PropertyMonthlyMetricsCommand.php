<?php

namespace App\Console\Commands;

use App\Jobs\RecalculatePropertyMetricsJob;
use App\Services\Metrics\PropertyMetricsService;
use Illuminate\Console\Command;

class PropertyMonthlyMetricsCommand extends Command
{
    protected $signature = 'property:monthly-metrics {--full : Recalculate all months present in bookings} {--sync : Run inline instead of queueing}';

    protected $description = 'Recalculate property monthly metrics from bookings (queued by default).';

    public function handle(PropertyMetricsService $metricsService): int
    {
        $full = (bool) $this->option('full');

        // Sync exception: --sync for local debugging / transactional integrity checks.
        if ($this->option('sync')) {
            $count = $metricsService->recalculate($full);
            $this->info("Updated {$count} property monthly metric row(s) synchronously.");

            return self::SUCCESS;
        }

        RecalculatePropertyMetricsJob::dispatch($full);
        $this->info('Queued RecalculatePropertyMetricsJob.');

        return self::SUCCESS;
    }
}
