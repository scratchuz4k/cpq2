<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Carbon;

class ProductsService
{
    public function __construct(protected PriceService $priceService) {}

    public function store(array $array): Product
    {
        $product = Product::create([
            ...$array,
            'expires_at' => $array['expires_at'] ? Carbon::parse($array['expires_at'])->endOfDay() : null,
        ]);

        if (array_key_exists('prices', $array) && is_array($array['prices'])) {
            foreach ($array['prices'] as $price) {
                $this->priceService->store([...$price, 'product_id' => $product->id]);
            }
        }

        return $product;
    }

    public function update(Product $product, array $array): Product
    {
        $product->update([
            ...$array,
            'expires_at' => $array['expires_at'] ? Carbon::parse($array['expires_at'])->endOfDay() : null,
            'category_id' => array_key_exists('category_id', $array) && ! is_null($array['category_id']) ? $array['category_id'] : $product->category_id,
        ]);

        return $product->refresh();
    }
}
