<?php

use Illuminate\Support\Facades\Artisan;

it('registers the phase 1 stub artisan commands', function (): void {
    $commands = array_keys(Artisan::all());

    expect($commands)
        ->toContain('alert:anti-gap')
        ->toContain('cloud:weekly-data-backup')
        ->toContain('dropbox:form-csv')
        ->toContain('property:monthly-metrics');
});

it('runs a stub command successfully', function (): void {
    $this->artisan('cron:test')
        ->assertSuccessful();
});
