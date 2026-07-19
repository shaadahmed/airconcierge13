<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\StubDomainCommand;
use Illuminate\Console\Command;

class DropboxFormSyncDbCommand extends Command
{
    use StubDomainCommand;

    protected $signature = 'dropbox:form-sync-db';

    protected $description = 'Phase 1 stub — implement in the matching domain phase.';

    public function handle(): int
    {
        return $this->reportStub();
    }
}
