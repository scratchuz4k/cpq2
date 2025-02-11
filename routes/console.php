<?php

use App\Console\Commands\DeleteOldFiles;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyTenMinutes();
Schedule::command(DeleteOldFiles::class)->weekly();
