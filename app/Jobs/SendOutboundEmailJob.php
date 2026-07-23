<?php

namespace App\Jobs;

use App\Models\OutboundEmailLog;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOutboundEmailJob implements ShouldQueue
{
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
            return;
        }

        $emailService->deliver($log, $this->mailData);
    }
}
