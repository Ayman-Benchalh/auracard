<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & cPanel Cron Scheduled Tasks
|--------------------------------------------------------------------------
|
| Scheduled tasks designed to run safely even on 5-minute cron cycles
| without overlapping using atomic locks.
|
*/

// Queue worker scheduled processing for cPanel environments without daemon supervisors
// Runs jobs and terminates when queue is empty, respecting a 280s window to avoid overlapping 5-minute crons
Schedule::command('queue:work --stop-when-empty --max-time=280 --memory=128')
    ->everyFiveMinutes()
    ->withoutOverlapping(60);

// Expired password reset tokens & auth housekeeping
Schedule::command('auth:clear-resets')
    ->everyFifteenMinutes();
