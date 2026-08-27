<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('soukelkom:cancel-unshipped-items')->hourly();
Schedule::command('soukelkom:release-earnings')->dailyAt('03:00');
