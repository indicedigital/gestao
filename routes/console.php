<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('contracts:generate-recurring-receivables')->dailyAt('06:00');
Schedule::command('expenses:generate-fixed-payables')->dailyAt('06:05');
