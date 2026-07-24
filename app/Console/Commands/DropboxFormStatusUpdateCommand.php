<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDropboxCsvJob;
use Illuminate\Console\Command;

class DropboxFormStatusUpdateCommand extends Command
{
    protected $signature = 'dropbox:form-status-update';

    protected $description = 'Queue Dropbox form status updates.';

    public function handle(): int
    {
        ProcessDropboxCsvJob::dispatch('status-update');
        $this->info('Queued ProcessDropboxCsvJob (status-update).');

        return self::SUCCESS;
    }
}
