<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductsService
{
    public function store(array $array): Product
    {
        $product = Product::create($array);
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
