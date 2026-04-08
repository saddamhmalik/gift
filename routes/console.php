<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Woohoo (QC): weekly OAuth refresh + product list + product details (token TTL 7 days)
Schedule::command('giftbox:woohoo-token')->weekly()->sundays()->at('02:00');
Schedule::command('giftbox:fetch-products')->weekly()->sundays()->at('03:00');
Schedule::command('giftbox:fetch-product-details')->weekly()->sundays()->at('04:00');

// Catalog cron jobs
Schedule::command('catalog:recalculate-trending')->everySixHours();
Schedule::command('catalog:expire-hot-deals')->hourly();
Schedule::command('catalog:warmup-cache')->everySixHours();
