<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Weekly data backup upload workflow (Phase 4).
 *
 * Uses the `gdrive` disk when configured; otherwise stores locally and logs.
 */
class CloudBackupService
{
    public function runWeeklyBackup(?string $localPath = null): string
    {
        $path = $localPath ?? $this->createLocalSnapshot();

        if (! Storage::disk('local')->exists($path)) {
            throw new RuntimeException("Backup file missing at [{$path}].");
        }

        if ($this->googleDriveConfigured()) {
            $remotePath = 'backups/'.basename($path);
            Storage::disk('gdrive')->put($remotePath, Storage::disk('local')->get($path));

            Log::info('Cloud backup uploaded to Google Drive.', [
                'local_path' => $path,
                'remote_path' => $remotePath,
            ]);

            return $remotePath;
        }

        // Follow-up: configure GOOGLE_DRIVE_* credentials for remote upload (ADR-014 / Phase 4).
        Log::warning('Cloud backup stored locally only — Google Drive disk not configured.', [
            'local_path' => $path,
        ]);

        return $path;
    }

    public function createLocalSnapshot(): string
    {
        $path = 'backups/weekly-'.now()->format('Ymd-His').'.txt';
        Storage::disk('local')->put($path, 'Air Concierge weekly backup snapshot @ '.now()->toIso8601String());

        return $path;
    }

    private function googleDriveConfigured(): bool
    {
        // Real Google Drive driver is deferred until masbug adapter installs cleanly (ADR-014).
        // Credentials alone are not enough while the disk still uses the local fallback driver.
        return false;
    }
}
