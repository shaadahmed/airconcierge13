<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Weekly data backup upload workflow (Phase 4).
 *
 * Uses the `gdrive` disk when the Google Drive adapter is installed and credentials
 * are present; otherwise stores locally and logs (ADR-014).
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
        if (! class_exists('Masbug\\Flysystem\\GoogleDriveAdapter')) {
            return false;
        }

        $disk = config('filesystems.disks.gdrive', []);

        if (($disk['driver'] ?? null) !== 'google') {
            return false;
        }

        return filled($disk['clientId'] ?? null)
            && filled($disk['clientSecret'] ?? null)
            && filled($disk['refreshToken'] ?? null);
    }
}
