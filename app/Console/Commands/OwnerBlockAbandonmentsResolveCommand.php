<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\StubDomainCommand;
use Illuminate\Console\Command;

class OwnerBlockAbandonmentsResolveCommand extends Command
{
    use StubDomainCommand;

    protected $signature = 'owner-block-abandonments:resolve';

    protected $description = 'Phase 1 stub — implement in the matching domain phase.';

    public function handle(): int
    {
        return $this->reportStub();
    }
}
