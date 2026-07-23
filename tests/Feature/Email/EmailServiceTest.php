<?php

use App\Mail\OutboundEmailMailable;
use App\Models\OutboundEmailLog;
use App\Services\Email\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sends mail through laravel mail and logs outbound email', function (): void {
    Mail::fake();

    $log = app(EmailService::class)->send([
        'to' => 'owner@example.com',
        'subject' => 'Hello',
        'body' => '<p>Body</p>',
        'source' => 'test',
    ]);

    expect($log->status)->toBe(OutboundEmailLog::STATUS_SENT)
        ->and($log->to_addresses)->toBe('owner@example.com');

    Mail::assertSent(OutboundEmailMailable::class, function (OutboundEmailMailable $mail): bool {
        return $mail->mailData['subject'] === 'Hello';
    });
});
