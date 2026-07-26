<?php

namespace App\Services\Dropbox;

use Illuminate\Support\Facades\Log;

/**
 * HelloWorks workflow CSV pipeline (legacy name: DropboxForm).
 *
 * Schedule→Job plumbing is live. Full domain parity is deferred until:
 * - HELLOWORKS_* credentials live in config (never hardcode legacy secrets)
 * - dropbox_form_list / dropbox_form_transations migrations are copied into live migrations
 * - Chronology mail status update path is verified against L13 schema
 */
class DropboxFormService
{
    public function processCsv(): void
    {
        $this->defer('processCsv', 'HelloWorks CSV download requires config credentials + storage path port');
    }

    public function syncDatabase(): void
    {
        $this->defer('syncDatabase', 'dropbox_form_transations table not in live migrations');
    }

    public function updateStatuses(): void
    {
        $this->defer('updateStatuses', 'status updates depend on synced HelloWorks rows + chronology_mail fields');
    }

    private function defer(string $method, string $reason): void
    {
        Log::info("DropboxFormService::{$method} deferred.", [
            'reason' => $reason,
            'note' => 'Pipeline ready; domain body pending schema + HelloWorks config approval.',
        ]);
    }
}
