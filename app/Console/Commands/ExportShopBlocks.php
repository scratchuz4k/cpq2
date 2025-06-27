<?php

namespace App\Console\Commands;

use App\Models\ShopBlocks\Brand;
use App\Models\ShopBlocks\Model;
use App\Models\ShopBlocks\Product;
use App\Models\ShopBlocks\ProductFeature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExportShopBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:shopblocks';

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

        $items = Product::where('category_id', '=', 22)->get();
        file_put_contents(storage_path('mocks/shopblocks.products.json'), json_encode($items->toArray()));

        $items = Model::whereHas('products', function ($query) {
            return $query->where('category_id', '=', 22);
        })->get();
        file_put_contents(storage_path('mocks/shopblocks.models.json'), json_encode($items->toArray()));

        $items = Brand::whereHas('products', function ($query) {
            return $query->where('category_id', '=', 22);
        })->get();
        file_put_contents(storage_path('mocks/shopblocks.brands.json'), json_encode($items->toArray()));

        $items = ProductFeature::whereHas('product', function ($query) {
            return $query->where('category_id', '=', 22);
        })->get();
        file_put_contents(storage_path('mocks/shopblocks.product_features.json'), json_encode($items->toArray()));

        Log::info('Job Finished.');
    }
}
