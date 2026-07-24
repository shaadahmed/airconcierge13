<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

/**
 * Slack alert for permanently failed queue jobs (Phase 4).
 *
 * Intentionally sync — queuing failure alerts risks nested failures.
 */
class QueueJobFailedNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $jobClass,
        public string $exceptionMessage,
        public array $context = [],
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if (blank(config('services.slack.notifications.bot_user_oauth_token'))) {
            return [];
        }

        return ['slack'];
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $correlationId = (string) ($this->context['correlation_id'] ?? 'n/a');

        return (new SlackMessage)
            ->text(sprintf('Queue job failed: %s', class_basename($this->jobClass)))
            ->headerBlock('Queue job failed')
            ->sectionBlock(function ($block): void {
                $block->text(sprintf(
                    "*%s*\n%s",
                    $this->jobClass,
                    $this->exceptionMessage,
                ))->markdown();
            })
            ->sectionBlock(function ($block) use ($correlationId): void {
                $block->field('*Environment*\n'.config('app.env'))->markdown();
                $block->field("*Correlation ID*\n{$correlationId}")->markdown();
            });
    }
}
