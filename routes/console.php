<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('kanban:send-due-reminders')->hourly();

Schedule::command('hr:accrue-leaves')->monthlyOn(1, '01:00');
Schedule::command('hr:carry-forward-leaves')->yearlyOn(1, 1, '02:00');
Schedule::command('hr:expire-comp-offs')->dailyAt('03:00');
Schedule::command('hr:birthday-anniversary-notify')->dailyAt('08:00');
