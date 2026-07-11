<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('steam:fetch-stats')
    ->dailyAt('20:00')
    ->timezone('Asia/Barnaul');