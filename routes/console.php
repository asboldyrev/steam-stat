<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('steam:fetch-stats')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('steam:sync-artwork')
    ->dailyAt('04:00')
    ->withoutOverlapping();
