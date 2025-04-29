<?php

use App\Console\Commands\DeleteOldFiles;
use App\Console\Commands\SyncShopBlocksProducts;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyTenMinutes();
Schedule::command(DeleteOldFiles::class)->weekly();
Schedule::command(SyncShopBlocksProducts::class)->dailyAt('06:00');
