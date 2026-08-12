<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/** Cloudflare's figure is a daily aggregate, so asking hourly
 * fetched the same number twenty-four times and rewrote the same
 * rows with it. */
Schedule::command('analytics:snapshot')->daily()->withoutOverlapping();
Schedule::command('activitylog:clean', ['--force'])->dailyAt('03:15')->withoutOverlapping();
Schedule::command('instagram:import')->dailyAt('03:30')->withoutOverlapping();
/**
 * No timeout is set on the YouTube calls, so a slow morning could
 * leave a run going for half an hour. Without this the next hour's
 * run starts anyway, and each one costs about 67 MB on a box with
 * room for six workers.
 */
Schedule::command('youtube:import')->hourly()->withoutOverlapping();
