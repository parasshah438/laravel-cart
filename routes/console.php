<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ================================================================================================
// 📋 SCHEDULED TASKS FOR SHIPPING SYSTEM
// ================================================================================================

// Update shipping tracking every 30 minutes
Schedule::command('shipping:update-tracking')->everyThirtyMinutes();

// Clean old cart data daily at 2 AM
Schedule::command('cart:clean')->dailyAt('02:00');

// Clean old recently viewed products weekly
Schedule::command('cleanup:recently-viewed')->weekly();

// Log scheduled tasks
Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('Scheduled tasks heartbeat', [
        'timestamp' => now(),
        'status' => 'running'
    ]);
})->hourly();

// Run queue work every minute
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
