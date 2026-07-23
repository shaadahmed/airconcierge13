<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OutboundEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{to: string|list<string>, subject: string, body: string, cc?: string|list<string>|null, bcc?: string|list<string>|null, from?: array{email: string, name?: string}|null, replyTo?: array{email: string, name?: string}|null, attachments?: list<array{fileName: string, fileContent: string}>|null}  $mailData
     */
    public function __construct(public array $mailData) {}

    public function envelope(): Envelope
    {
        $from = $this->mailData['from'] ?? null;
        $replyTo = $this->mailData['replyTo'] ?? null;

        return new Envelope(
            subject: $this->mailData['subject'],
            from: is_array($from) ? new Address($from['email'], $from['name'] ?? '') : null,
            replyTo: is_array($replyTo) ? [new Address($replyTo['email'], $replyTo['name'] ?? '')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->mailData['body'],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->mailData['attachments'] ?? [] as $attachment) {
            $attachments[] = Attachment::fromData(
                fn (): string => $attachment['fileContent'],
                $attachment['fileName'],
            );
        }

        return $attachments;
    }
}
