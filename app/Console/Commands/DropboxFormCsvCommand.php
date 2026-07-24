<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDropboxCsvJob;
use Illuminate\Console\Command;

class DropboxFormCsvCommand extends Command
{
    protected $signature = 'dropbox:form-csv';

    protected $description = 'Queue Dropbox form CSV processing.';

    public function handle(): int
    {
        ProcessDropboxCsvJob::dispatch('csv');
        $this->info('Queued ProcessDropboxCsvJob (csv).');

        return self::SUCCESS;
    }
}
