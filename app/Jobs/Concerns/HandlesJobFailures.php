<?php

namespace App\Jobs\Concerns;

use App\Notifications\QueueJobFailedNotification;
use Illuminate\Notifications\Slack\SlackRoute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Phase 4 job standards: retries, structured logging, Slack on final failure.
 */
trait HandlesJobFailures
{
    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 60, 180];

    public int $timeout = 120;

    public string $correlationId = '';

    /**
     * @return array<string, mixed>
     */
    protected function jobLogContext(array $extra = []): array
    {
        return array_filter([
            'job' => static::class,
            'correlation_id' => $this->jobCorrelationId(),
            ...$extra,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    protected function jobCorrelationId(): string
    {
        if ($this->correlationId === '') {
            $this->correlationId = (string) Str::uuid();
        }

        return $this->correlationId;
    }

    public function failed(?Throwable $exception): void
    {
        $context = $this->jobLogContext([
            'exception' => $exception?->getMessage(),
            'exception_class' => $exception !== null ? $exception::class : null,
        ]);

        Log::error('Queued job failed permanently.', $context);

        $token = config('services.slack.notifications.bot_user_oauth_token');
        $channel = config('services.slack.notifications.channel');

        if (blank($token) || blank($channel)) {
            return;
        }

        try {
            Notification::route('slack', SlackRoute::make($channel, $token))
                ->notify(new QueueJobFailedNotification(
                    jobClass: static::class,
                    exceptionMessage: $exception?->getMessage() ?? 'Unknown failure',
                    context: $context,
                ));
        } catch (Throwable $notificationException) {
            // Fail soft when Slack is misconfigured — logging above remains the source of truth.
            Log::warning('Failed to send Slack job-failure notification.', [
                'job' => static::class,
                'error' => $notificationException->getMessage(),
            ]);
        }
    }
}
