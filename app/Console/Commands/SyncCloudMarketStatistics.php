<?php

namespace App\Console\Commands;

use App\Jobs\CloudMarketStatistics;
use Illuminate\Console\Command;

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
        CloudMarketStatistics::dispatch();
    }
}
