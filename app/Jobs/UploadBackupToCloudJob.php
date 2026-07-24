<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Backup\CloudBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UploadBackupToCloudJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    public function __construct(public ?string $localPath = null)
    {
        $this->timeout = 600;
    }

    public function handle(CloudBackupService $backupService): void
    {
        Log::info('UploadBackupToCloudJob starting.', $this->jobLogContext([
            'local_path' => $this->localPath,
        ]));

        $backupService->runWeeklyBackup($this->localPath);
    }
}
