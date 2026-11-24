<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Needs a cron entry on the server running `php artisan schedule:run` (see doc/deployment-checklist.md).
// Time is in app.timezone. The command itself limits each person to one reminder a week.
Schedule::command('picks:send-reminders')->dailyAt('16:00');
