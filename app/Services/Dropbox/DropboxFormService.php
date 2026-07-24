<?php

namespace App\Services\Dropbox;

use Illuminate\Support\Facades\Log;

/**
 * Dropbox form CSV workflows (Phase 4 async pipeline).
 *
 * Domain parity with legacy DropboxFormCSVController is a follow-up card.
 */
class DropboxFormService
{
    public function processCsv(): void
    {
        Log::info('DropboxFormService::processCsv — pipeline ready; domain body follow-up.');
    }

    public function syncDatabase(): void
    {
        Log::info('DropboxFormService::syncDatabase — pipeline ready; domain body follow-up.');
    }

    public function updateStatuses(): void
    {
        Log::info('DropboxFormService::updateStatuses — pipeline ready; domain body follow-up.');
    }
}
