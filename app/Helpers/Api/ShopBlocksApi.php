<?php

namespace App\Helpers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopBlocksApi
{
    protected $requestTimeout = 420;

    public function getArticles($limit = 10, $page = 1, $filters = [])
    {
        $params = [
            'page' => $page,
            'perPage' => $limit,
            'fields' => 'slug,sku,name,price,created,product_category_id,brand_id,stock,primary_image,updated',
            'active' => 1,
        ];

        foreach ($filters as $key => $value) {
            if ($value == null) {
                continue;
            }
            $params[$key] = $value;
        }

        $response = Http::withOptions([
            'timeout' => $this->requestTimeout,
            'connect_timeout' => 30,
        ])->withToken(env('SHOPBLOCKS_API_KEY'))->get('https://api.shopblocks.com/api/v2/products', $params);

        if (! $response->successful()) {
            // Handle the error accordingly. For now, we'll just log and return an empty array.
            Log::error('Failed to retrieve products from Shopblocks API: '.$response->body());

            return [];
        }
        $temp = $response->json();
        if (array_key_exists('data', $temp['data'])) {
            foreach ($temp['data'] as $key => $value) {
                $temp[$key] = $value;
            }
        }

        return $temp;
    }

    public function getCategories($limit = 10, $page = 1)
    {
        $params = [
            'page' => $page,
            'perPage' => $limit,
        ];

        $response = Http::withOptions([
            'timeout' => $this->requestTimeout,
            'connect_timeout' => 30,
        ])->withToken(env('SHOPBLOCKS_API_KEY'))->get('https://api.symphonycommerce.io/api/v2/categories', $params);

        if (! $response->successful()) {
            // Handle the error accordingly. For now, we'll just log and return an empty array.
            Log::error('Failed to retrieve categories from Shopblocks API: '.$response->body());

            return [];
        }

        return $response->json();
    }
}
