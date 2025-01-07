<?php

namespace App\Services;

use App\Models\Product;

class ProductsService
{
    public function __construct(protected PriceService $priceService) {}
    public function store(array $array): Product
    {
        $product = Product::create($array);
        if (array_key_exists('categories', $array)) {
            $product->categories()->sync($array['categories']);
        }

        if (array_key_exists('prices', $array) && is_array($array['prices'])) {
            foreach ($array['prices'] as $price) {
                $this->priceService->store([...$price, 'product_id' => $product->id]);
            }
        }

        return $product;
    }

    public function update(Product $product, array $array): Product
    {
        $product->update($array);
        if (array_key_exists('categories', $array)) {
            $product->categories()->sync($array['categories']);
        }
        return $product->refresh();
    }
}
