<?php

namespace App\Helpers\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ShopBlocksApi
{
    public function get(int $category_id, $limit = 10, $page = 1)
    {
        $params = [
            'page' => $page,
            'perPage' => $limit,
            'fields' => 'slug,sku,name,price,created,product_category_id,brand_id,stock,primary_image',
            'search_fields' => [
                'product_category_id' => $category_id,
            ]
        ];
        $response = Http::withToken(env('SHOPBLOCKS_API_KEY'))
            ->get('https://api.shopblocks.com/api/v2/products/simplified', $params);

        if (!$response->successful()) {
            // Handle the error accordingly. For now, we'll just log and return an empty array.
            Log::error('Failed to retrieve products from Shopblocks API: ' . $response->body());
            return [];
        }
        return $response->json();
    }

}
