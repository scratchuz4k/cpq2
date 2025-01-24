<?php

use App\Console\Commands\DeleteOldFiles;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose(description: 'Display an inspiring quote')->hourly();

Schedule::command(DeleteOldFiles::class)->daily();
