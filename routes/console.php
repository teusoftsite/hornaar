<?php

use Illuminate\Support\Facades\Schedule;

// Cron op de server: * * * * * cd /pad/naar/app && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('facturen:maand')->monthlyOn(1, '06:00')->timezone('Europe/Amsterdam');
Schedule::command('facturen:herinneringen')->dailyAt('07:00')->timezone('Europe/Amsterdam');
Schedule::command('privacy:opschonen')->dailyAt('03:00')->timezone('Europe/Amsterdam');
