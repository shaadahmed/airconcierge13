<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\StubDomainCommand;
use Illuminate\Console\Command;

class PropertyMonthlyMetricsCommand extends Command
{
    use StubDomainCommand;

    protected $signature = 'property:monthly-metrics {--full : Rebuild a fuller history when domain logic lands}';

    protected $description = 'Phase 1 stub — implement in the matching domain phase.';

    public function handle(): int
    {
        return $this->reportStub();
    }
}
