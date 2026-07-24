<?php

use App\Jobs\CompressImagesBatchJob;
use App\Jobs\GeneratePdfJob;
use App\Jobs\ProcessDropboxCsvJob;
use App\Jobs\RecalculatePropertyMetricsJob;
use App\Jobs\RunScheduledAlertJob;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendOutboundEmailJob;
use App\Jobs\UploadBackupToCloudJob;
use App\Mail\OutboundEmailMailable;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\OutboundEmailLog;
use App\Models\User;
use App\Notifications\QueueJobFailedNotification;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('queues outbound email when EmailService send uses queue flag', function (): void {
    Queue::fake();

    app(EmailService::class)->send([
        'to' => 'owner@example.com',
        'subject' => 'Queued',
        'body' => '<p>Body</p>',
        'source' => 'test',
    ], queue: true);

    Queue::assertPushed(SendOutboundEmailJob::class);
});

it('delivers queued outbound email through SendOutboundEmailJob', function (): void {
    Mail::fake();

    $log = OutboundEmailLog::query()->create([
        'status' => OutboundEmailLog::STATUS_PENDING,
        'subject' => 'Hello',
        'body' => '<p>Body</p>',
        'to_addresses' => 'owner@example.com',
        'source' => 'test',
    ]);

    (new SendOutboundEmailJob($log->id, [
        'to' => 'owner@example.com',
        'subject' => 'Hello',
        'body' => '<p>Body</p>',
    ]))->handle(app(EmailService::class));

    expect($log->fresh()->status)->toBe(OutboundEmailLog::STATUS_SENT);
    Mail::assertSent(OutboundEmailMailable::class);
});

it('sends notification email through SendNotificationJob', function (): void {
    Mail::fake();

    (new SendNotificationJob([
        'to' => 'owner@example.com',
        'subject' => 'Notice',
        'body' => '<p>Hi</p>',
        'source' => 'notification-test',
    ]))->handle(app(EmailService::class));

    Mail::assertSent(OutboundEmailMailable::class);
});

it('generates a payment receipt pdf via GeneratePdfJob', function (): void {
    Storage::fake('local');

    $payment = BookingPayment::factory()->create();

    (new GeneratePdfJob("payment-receipt:{$payment->id}"))->handle(app(PdfService::class));

    $receipt = $payment->fresh()->receipt;
    expect($receipt)->not->toBeNull()
        ->and($receipt->receipt_path)->toBe("receipts/payment-{$payment->id}.pdf");

    Storage::disk('local')->assertExists($receipt->receipt_path);
});

it('dispatches metrics job from property:monthly-metrics command', function (): void {
    Queue::fake();

    $this->artisan('property:monthly-metrics')->assertSuccessful();

    Queue::assertPushed(RecalculatePropertyMetricsJob::class);
});

it('dispatches dropbox, backup, image, and alert jobs from commands', function (): void {
    Queue::fake();

    $this->artisan('dropbox:form-csv')->assertSuccessful();
    $this->artisan('cloud:weekly-data-backup')->assertSuccessful();
    $this->artisan('images:compress-uploads', ['--limit' => 250, '--batch' => 100])->assertSuccessful();
    $this->artisan('alert:anti-gap')->assertSuccessful();

    Queue::assertPushed(ProcessDropboxCsvJob::class);
    Queue::assertPushed(UploadBackupToCloudJob::class);
    Queue::assertPushed(CompressImagesBatchJob::class, 3);
    Queue::assertPushed(RunScheduledAlertJob::class, fn (RunScheduledAlertJob $job): bool => $job->alertKey === 'anti-gap');
});

it('allows superadmin to view failed jobs and forbids others', function (): void {
    DB::table('failed_jobs')->insert([
        'uuid' => '11111111-1111-1111-1111-111111111111',
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\SendOutboundEmailJob']),
        'exception' => 'Example failure',
        'failed_at' => now(),
    ]);

    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($superAdmin)
        ->getJson(route('admin.failed-jobs.index'))
        ->assertOk()
        ->assertJsonFragment(['display_name' => 'App\\Jobs\\SendOutboundEmailJob']);

    $this->actingAs($admin)
        ->getJson(route('admin.failed-jobs.index'))
        ->assertForbidden();
});

it('notifies slack route when a job fails permanently', function (): void {
    Notification::fake();
    config([
        'services.slack.notifications.bot_user_oauth_token' => 'xoxb-test',
        'services.slack.notifications.channel' => '#queue-failures',
    ]);

    $job = new SendNotificationJob(['to' => 'a@b.c', 'subject' => 'x', 'body' => 'y']);
    $job->failed(new RuntimeException('boom'));

    Notification::assertSentOnDemand(QueueJobFailedNotification::class);
});

it('queues payment receipt pdf when booking payment is created without path', function (): void {
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $booking = Booking::factory()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.payments.store'), [
            'booking_id' => $booking->id,
            'amount' => 42.00,
            'payment_date' => now()->toDateString(),
        ])
        ->assertCreated();

    Queue::assertPushed(GeneratePdfJob::class);
});
