<?php

use App\Console\Commands\DeleteOldFiles;
use App\Console\Commands\SyncCloudMarketStatistics;
use App\Console\Commands\SyncShopBlocks;
use App\Console\Commands\UpdateStatusExpiredProduct;
use App\Console\Commands\UpdateStatusExpiredQuotes;
use Illuminate\Support\Facades\Schedule;

Schedule::command('horizon:snapshot')->everyTenMinutes();
Schedule::command(DeleteOldFiles::class)->weekly()->sentryMonitor();
Schedule::command(SyncShopBlocks::class)->dailyAt('06:00')->sentryMonitor();
Schedule::command(UpdateStatusExpiredQuotes::class)->dailyAt('00:00')->sentryMonitor();
Schedule::command(UpdateStatusExpiredProduct::class)->dailyAt('00:05')->sentryMonitor();
Schedule::command(SyncCloudMarketStatistics::class)->dailyAt('00:10')->sentryMonitor();
