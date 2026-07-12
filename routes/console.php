<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('steam:fetch-stats')->hourly();
