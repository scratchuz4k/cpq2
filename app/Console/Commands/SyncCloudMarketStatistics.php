<?php

namespace App\Console\Commands;

use App\Jobs\CloudMarketStatistics;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncCloudMarketStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:cloudmarket-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches CloudMarket response statistics from the API and saves them to MongoDB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = now();
        Log::info("SyncCloudMarketStatistics | Started at $start");

        CloudMarketStatistics::dispatch();

        $end = now();
        $date1 = Carbon::parse($start);
        $date2 = Carbon::parse($end);
        $diffInSeconds = $date2->diff($date1); // Output: 3600
        $time = $diffInSeconds->format('%y-%m-%d %h:%i:%s');
        Log::info("SyncCloudMarketStatistics | Ended at $end | Elapsed Time: $time");
    }
}
