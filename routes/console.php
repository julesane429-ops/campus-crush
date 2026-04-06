<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('crush:daily-match')->dailyAt('08:00');
Schedule::command('crush:feature-reminders')->dailyAt('12:30');

// Engagement quotidien (midi)
Schedule::command('crush:engagement-push')->dailyAt('12:00');

// Samedi soir (2ème notification)
Schedule::command('crush:engagement-push')->saturdays()->at('19:00');