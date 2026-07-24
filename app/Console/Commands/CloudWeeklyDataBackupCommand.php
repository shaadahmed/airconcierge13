<?php

namespace App\Console\Commands;

use App\Jobs\UploadBackupToCloudJob;
use Illuminate\Console\Command;

class CloudWeeklyDataBackupCommand extends Command
{
    protected $signature = 'cloud:weekly-data-backup';

    protected $description = 'Queue weekly data backup upload to cloud storage.';

    public function handle(): int
    {
        UploadBackupToCloudJob::dispatch();
        $this->info('Queued UploadBackupToCloudJob.');

        return self::SUCCESS;
    }
}
