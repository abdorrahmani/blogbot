<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('telegram:poll')->everyMinute();
