<?php

namespace App\Services\Email;

use App\Jobs\SendOutboundEmailJob;
use App\Mail\OutboundEmailMailable;
use App\Models\OutboundEmailLog;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailService
{
    /**
     * @param  array{to: string|list<string>, subject: string, body: string, cc?: string|list<string>|null, bcc?: string|list<string>|null, from?: array{email: string, name?: string}|null, replyTo?: array{email: string, name?: string}|null, attachments?: list<array{fileName: string, fileContent: string}>|null, source?: string|null, metadata?: array<string, mixed>|null}  $mailData
     */
    public function send(array $mailData, bool $queue = false): OutboundEmailLog
    {
        $log = OutboundEmailLog::query()->create([
            'status' => OutboundEmailLog::STATUS_PENDING,
            'subject' => $mailData['subject'],
            'body' => $mailData['body'],
            'to_addresses' => $this->stringifyAddresses($mailData['to']),
            'cc_addresses' => isset($mailData['cc']) ? $this->stringifyAddresses($mailData['cc']) : null,
            'bcc_addresses' => isset($mailData['bcc']) ? $this->stringifyAddresses($mailData['bcc']) : null,
            'from_email' => $mailData['from']['email'] ?? null,
            'from_name' => $mailData['from']['name'] ?? null,
            'reply_to' => isset($mailData['replyTo']) ? json_encode($mailData['replyTo']) : null,
            'source' => $mailData['source'] ?? null,
            'metadata' => $mailData['metadata'] ?? null,
        ]);

        if ($queue) {
            SendOutboundEmailJob::dispatch($log->id, $mailData);

            return $log;
        }

        return $this->deliver($log, $mailData);
    }

    /**
     * @param  array{to: string|list<string>, subject: string, body: string, cc?: string|list<string>|null, bcc?: string|list<string>|null, from?: array{email: string, name?: string}|null, replyTo?: array{email: string, name?: string}|null, attachments?: list<array{fileName: string, fileContent: string}>|null}  $mailData
     */
    public function deliver(OutboundEmailLog $log, array $mailData): OutboundEmailLog
    {
        try {
            $mailable = new OutboundEmailMailable($mailData);
            $pending = Mail::to($mailData['to']);

            if (! empty($mailData['cc'])) {
                $pending->cc($mailData['cc']);
            }

            if (! empty($mailData['bcc'])) {
                $pending->bcc($mailData['bcc']);
            }

            $pending->send($mailable);
            $log->markSent();
        } catch (Throwable $exception) {
            $log->markFailed($exception->getMessage());
            throw $exception;
        }

        return $log->fresh() ?? $log;
    }

    /**
     * @param  string|list<string>  $addresses
     */
    private function stringifyAddresses(string|array $addresses): string
    {
        return is_array($addresses) ? implode(',', $addresses) : $addresses;
    }
}
