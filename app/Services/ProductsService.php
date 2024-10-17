<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductsService
{
    public function store(array $array): Product
    {
        return DB::transaction(function () use ($array) {
            $product = Product::create($array);
            return $product;
        });
    }

    public function update(Product $product, array $array): Product
    {
        return DB::transaction(function () use ($product, $array) {
            $product->update($array);
            return $product;
        });
    }
}
