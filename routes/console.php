<?php

use App\Console\Commands\DeleteOldFiles;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Schedule::command(DeleteOldFiles::class)->daily();
