<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertAntiGapCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:anti-gap';

    protected $description = 'Queue scheduled alert: anti-gap.';

    public function handle(): int
    {
        return $this->dispatchAlert('anti-gap');
    }
}
