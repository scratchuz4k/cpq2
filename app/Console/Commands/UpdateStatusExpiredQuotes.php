<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatusExpiredQuotesJob;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStatusExpiredQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-status-expired-quotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Quote status if expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = now();
        Log::info("UpdateStatusExpiredQuotesJob | Started at $start");

        foreach (Tenant::all() as $tenant) {
            $tenant->makeCurrent();
            UpdateStatusExpiredQuotesJob::dispatch();
            $tenant->forgetCurrent();
        }
        $end = now();
        $date1 = Carbon::parse($start);
        $date2 = Carbon::parse($end);
        $diffInSeconds = $date2->diff($date1); // Output: 3600
        $time = $diffInSeconds->format('%y-%m-%d %h:%i:%s');
        Log::info("UpdateStatusExpiredQuotesJob | Ended at $end | Elapsed Time: $time");

    }
}
