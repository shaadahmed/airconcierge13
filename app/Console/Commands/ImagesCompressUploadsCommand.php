<?php

namespace App\Console\Commands;

use App\Jobs\CompressImagesBatchJob;
use Illuminate\Console\Command;

class ImagesCompressUploadsCommand extends Command
{
    protected $signature = 'images:compress-uploads {--limit=1000 : Max images to process across batches} {--batch=100 : Images per queued job}';

    protected $description = 'Dispatch per-batch image compression jobs.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $batch = max(1, (int) $this->option('batch'));
        $dispatched = 0;

        for ($remaining = $limit; $remaining > 0; $remaining -= $batch) {
            CompressImagesBatchJob::dispatch(min($batch, $remaining));
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} CompressImagesBatchJob(s).");

        return self::SUCCESS;
    }
}
