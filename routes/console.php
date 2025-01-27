<?php

use App\Console\Commands\DeleteOldFiles;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command(DeleteOldFiles::class)->daily();
