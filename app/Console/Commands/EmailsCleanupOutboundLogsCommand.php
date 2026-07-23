<?php

namespace App\Console\Commands;

use App\Models\OutboundEmailLog;
use Illuminate\Console\Command;

class EmailsCleanupOutboundLogsCommand extends Command
{
    protected $signature = 'emails:cleanup-outbound-logs {--days=30 : Delete logs older than this many days}';

    protected $description = 'Delete outbound email logs older than the retention window.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $deleted = OutboundEmailLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} outbound email log(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
