<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('analytics:snapshot')->dailyAt('03:00');
Schedule::command('activitylog:clean', ['--force'])->dailyAt('03:15');
Schedule::command('instagram:import')->dailyAt('03:30');
Schedule::command('youtube:import')->dailyAt('04:00');
