<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatusExpiredProductsJob;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStatusExpiredProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-status-expired-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Product status if expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = now();
        Log::info("UpdateStatusExpiredProductsJob | Started at $start");

        foreach (Tenant::all() as $tenant) {
            $tenant->makeCurrent();
            UpdateStatusExpiredProductsJob::dispatchSync();
            $tenant->forgetCurrent();
        }
        $end = now();
        $date1 = Carbon::parse($start);
        $date2 = Carbon::parse($end);
        $diffInSeconds = $date2->diff($date1); // Output: 3600
        $time = $diffInSeconds->format('%y-%m-%d %h:%i:%s');
        Log::info("UpdateStatusExpiredProductsJob | Ended at $end | Elapsed Time: $time");

    }
}
