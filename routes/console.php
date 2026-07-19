<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Schedule frequencies aligned to known business schedules.
| Command bodies are stubs until domain phases implement them.
| No public /cron/* HTTP endpoints in this application.
*/

Schedule::command('emails:process-reviews')->hourly()->withoutOverlapping();
Schedule::command('owners:payout')->dailyAt('20:00')->withoutOverlapping();
Schedule::command('alert:anti-gap')->dailyAt('01:00');
Schedule::command('alert:booking-conflict')->dailyAt('09:00');
Schedule::command('alert:business-license-expiry')->dailyAt('09:10');
Schedule::command('emails:limit-owner-block')->dailyAt('00:00');
Schedule::command('property:monthly-metrics')->dailyAt('00:00')->withoutOverlapping();
Schedule::command('property:monthly-metrics --full')
    ->weeklyOn(0, '02:30')
    ->withoutOverlapping();
Schedule::command('owner-block-abandonments:resolve')->dailyAt('00:30')->withoutOverlapping();
Schedule::command('images:compress-uploads --limit=1000')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('emails:cleanup-outbound-logs')->dailyAt('02:00');

// Former public HTTP cron alerts — CLI-only until domain logic lands.
Schedule::command('alert:recurring-payments')->dailyAt('06:00');
Schedule::command('alert:password-expiry')->dailyAt('06:05');
Schedule::command('alert:property-permit-expiry')->dailyAt('06:10');
Schedule::command('alert:property-vacancy')->dailyAt('06:15');
Schedule::command('alert:booking-month-difference')->dailyAt('06:20');
Schedule::command('alert:consecutive-guest-bookings')->dailyAt('06:25');
Schedule::command('alert:insurance-policy-expiry')->dailyAt('06:30');
Schedule::command('alert:security-deposit')->dailyAt('06:35');
Schedule::command('alert:owner-block-reminders')->dailyAt('06:40');
Schedule::command('alert:owner-block-extensions')->dailyAt('06:45');
Schedule::command('alert:property-audits')->dailyAt('06:50');
Schedule::command('alert:property-audit-reminders')->dailyAt('06:55');
Schedule::command('cloud:weekly-data-backup')->weeklyOn(0, '03:30');
Schedule::command('dropbox:form-csv')->hourly();
Schedule::command('dropbox:form-sync-db')->hourly();
Schedule::command('dropbox:form-status-update')->hourly();
