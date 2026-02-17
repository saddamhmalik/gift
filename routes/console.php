<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Catalog cron jobs
Schedule::command('catalog:recalculate-trending')->everySixHours();
Schedule::command('catalog:expire-hot-deals')->hourly();
Schedule::command('catalog:warmup-cache')->everySixHours();
