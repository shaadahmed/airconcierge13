<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DispatchesScheduledAlertJob;
use Illuminate\Console\Command;

class AlertPropertyVacancyCommand extends Command
{
    use DispatchesScheduledAlertJob;

    protected $signature = 'alert:property-vacancy';

    protected $description = 'Queue scheduled alert: property-vacancy.';

    public function handle(): int
    {
        return $this->dispatchAlert('property-vacancy');
    }
}
