<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatusExpiredQuotesJob;
use App\Models\Tenant;
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
        Log::info('UpdateStatusExpiredQuotesJob started at  '.now());

        foreach (Tenant::all() as $tenant) {
            $tenant->makeCurrent();
            UpdateStatusExpiredQuotesJob::dispatch();
            $tenant->forgetCurrent();
        }
        Log::info('UpdateStatusExpiredQuotesJob completed at '.now());
    }
}
