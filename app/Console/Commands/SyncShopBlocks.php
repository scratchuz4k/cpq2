<?php

namespace App\Console\Commands;

use App\Jobs\ShopBlocksCategoriesJob;
use App\Jobs\ShopBlocksProductsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShopBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:shopblocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and Updated ShopBlocks Products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::withContext(['signature' => $this->signature]);
        Log::info('Job Started.');

        ShopBlocksCategoriesJob::dispatch();
        ShopBlocksProductsJob::dispatch();

        Log::info('Job Finished.');
        Log::withContext([]);
    }
}
