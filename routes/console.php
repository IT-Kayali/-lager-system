<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reservations:release-expired')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('system:backup --automatic')
    ->everyMinute()
    ->withoutOverlapping(15);
