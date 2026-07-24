<?php

use App\Jobs\UploadBackupToCloudJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

it('registers the phase 1 stub artisan commands', function (): void {
    $commands = array_keys(Artisan::all());

    expect($commands)
        ->toContain('alert:anti-gap')
        ->toContain('cloud:weekly-data-backup')
        ->toContain('dropbox:form-csv')
        ->toContain('property:monthly-metrics');
});

it('runs cron:test successfully', function (): void {
    $this->artisan('cron:test')
        ->assertSuccessful();
});

it('queues cloud backup instead of stubbing', function (): void {
    Queue::fake();

    $this->artisan('cloud:weekly-data-backup')->assertSuccessful();

    Queue::assertPushed(UploadBackupToCloudJob::class);
});
