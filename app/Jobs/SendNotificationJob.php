<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array{to: string|list<string>, subject: string, body: string, source?: string|null, metadata?: array<string, mixed>|null}  $payload
     */
    public function __construct(public array $payload) {}

    public function handle(EmailService $emailService): void
    {
        $source = $this->payload['source'] ?? 'notification';

        Log::info('SendNotificationJob delivering notification.', $this->jobLogContext([
            'to' => $this->payload['to'],
            'source' => $source,
        ]));

        $emailService->send([
            'to' => $this->payload['to'],
            'subject' => $this->payload['subject'],
            'body' => $this->payload['body'],
            'source' => $source,
            'metadata' => $this->payload['metadata'] ?? null,
        ], queue: false);
    }
}
