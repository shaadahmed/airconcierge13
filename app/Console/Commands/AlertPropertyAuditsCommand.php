<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertPropertyAuditsCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:property-audits';

    protected $description = 'Queue scheduled alert: property-audits.';

    public function handle(): int
    {
        return $this->dispatchAlert('property-audits');
    }
}
