<?php

namespace App\Services\External;

use App\Helpers\Files\ShopBlocksFile;
use App\Jobs\ShopBlocksProductsJob;
use Exception;
use Illuminate\Http\Request;

class ShopBlocksService
{
    public function getArticles(Request $request)
    {
        $filename = 'shopblock_products.json';
        $path = storage_path('/app/products/');
        $file = new ShopBlocksFile($path, $filename);
        try {
            ShopBlocksProductsJob::dispatch($file);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return $file->getContent();
    }
}
