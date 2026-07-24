<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Models\OutboundEmailLog;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOutboundEmailJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array{to: string|list<string>, subject: string, body: string, cc?: string|list<string>|null, bcc?: string|list<string>|null, from?: array{email: string, name?: string}|null, replyTo?: array{email: string, name?: string}|null, attachments?: list<array{fileName: string, fileContent: string}>|null}  $mailData
     */
    public function __construct(
        public int $outboundEmailLogId,
        public array $mailData,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $log = OutboundEmailLog::query()->find($this->outboundEmailLogId);

        if ($log === null) {
            Log::info('SendOutboundEmailJob skipped — outbound log missing.', $this->jobLogContext([
                'entity_id' => $this->outboundEmailLogId,
            ]));

            return;
        }

        // Idempotent: already-sent logs are safe to skip on retry.
        if ($log->status === OutboundEmailLog::STATUS_SENT) {
            return;
        }

        Log::info('SendOutboundEmailJob delivering mail.', $this->jobLogContext([
            'entity_id' => $log->id,
            'source' => $log->source,
        ]));

        $emailService->deliver($log, $this->mailData);
    }
}
