<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly backup, every Sunday at 03:00 (uses the app timezone). Needs the scheduler to be running.
Schedule::command('backup:run')->weeklyOn(0, '03:00')->withoutOverlapping();
