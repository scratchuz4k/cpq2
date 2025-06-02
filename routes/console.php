<?php

use App\Console\Commands\DeleteOldFiles;
use App\Console\Commands\SyncShopBlocks;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyTenMinutes();
Schedule::command(DeleteOldFiles::class)->weekly();
Schedule::command(SyncShopBlocks::class)->dailyAt('06:00');
