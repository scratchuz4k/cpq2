<?php

namespace App\Services\External;

use App\Helpers\Files\ShopBlocksFile;
use App\Jobs\ShopBlocksProductsJob;
use Illuminate\Http\Request;

class ShopBlocksService
{
    public function getArticles(Request $request)
    {

        $filename = 'shopblock_products.json';
        $path = storage_path('/app/products/');
        $file = new ShopBlocksFile($path, $filename);
        ShopBlocksProductsJob::dispatch($file);
        // return ['items' => $products, 'filters' => $tempFilters];
        // return $this->cloudMarketRepository->sendResponse($file->getContent(), $filters);
    }
}
