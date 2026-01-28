<?php

namespace App\Console\Commands;

use App\Jobs\UpdateStatusExpiredProductsJob;
use App\Models\Tenant;
use Illuminate\Console\Command;

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
        foreach (Tenant::all() as $tenant) {
            $tenant->makeCurrent();
            UpdateStatusExpiredProductsJob::dispatch();
            $tenant->forgetCurrent();
        }
    }
}
