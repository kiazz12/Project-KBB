<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --frequency=daily')
    ->dailyAt('01:00')
    ->withoutOverlapping();

Schedule::command('backup:run --frequency=weekly')
    ->weeklyOn(1, '01:30')
    ->withoutOverlapping();

Schedule::command('backup:run --frequency=monthly')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping();

Schedule::command('backup:run --frequency=yearly')
    ->yearlyOn(1, 1, '03:00')
    ->withoutOverlapping();
