<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertPropertyAuditRemindersCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:property-audit-reminders';

    protected $description = 'Queue scheduled alert: property-audit-reminders.';

    public function handle(): int
    {
        return $this->dispatchAlert('property-audit-reminders');
    }
}
