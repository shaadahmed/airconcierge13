<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDropboxCsvJob;
use Illuminate\Console\Command;

class DropboxFormSyncDbCommand extends Command
{
    protected $signature = 'dropbox:form-sync-db';

    protected $description = 'Queue Dropbox form database sync.';

    public function handle(): int
    {
        ProcessDropboxCsvJob::dispatch('sync-db');
        $this->info('Queued ProcessDropboxCsvJob (sync-db).');

        return self::SUCCESS;
    }
}
