<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('telegram:poll')->everyMinute();
Schedule::command('telegram:send-scheduled-messages')->hourly();
