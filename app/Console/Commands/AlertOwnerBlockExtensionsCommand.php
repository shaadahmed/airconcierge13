<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertOwnerBlockExtensionsCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:owner-block-extensions';

    protected $description = 'Queue scheduled alert: owner-block-extensions.';

    public function handle(): int
    {
        return $this->dispatchAlert('owner-block-extensions');
    }
}
