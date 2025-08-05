<?php

namespace App\Services;

use App\Models\Product;

class ProductsService
{
    public function __construct(protected PriceService $priceService) {}

    public function store(array $array): Product
    {
        $product = Product::create($array);

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

        return $product->refresh();
    }
}
