<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertOwnerBlockRemindersCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:owner-block-reminders';

    protected $description = 'Queue scheduled alert: owner-block-reminders.';

    public function handle(): int
    {
        return $this->dispatchAlert('owner-block-reminders');
    }
}
