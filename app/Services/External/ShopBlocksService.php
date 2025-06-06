<?php

namespace App\Services\External;

use App\Jobs\ShopBlocksProductsJob;
use Exception;
use Illuminate\Http\Request;

class ShopBlocksService
{
    public function getArticles(Request $request)
    {
        try {
            ShopBlocksProductsJob::dispatch();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
