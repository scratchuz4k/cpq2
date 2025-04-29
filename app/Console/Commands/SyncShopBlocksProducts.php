<?php

namespace App\Console\Commands;

use App\Helpers\Files\ShopBlocksFile;
use App\Jobs\ShopBlocksProductsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShopBlocksProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:shopblocks-products';

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

        $filename = 'shopblock_products.json';
        $path = storage_path('/app/products/');
        $file = new ShopBlocksFile($path, $filename);
        ShopBlocksProductsJob::dispatch($file);

        Log::info('Job Finished.');
        Log::withContext([]);
    }
}
