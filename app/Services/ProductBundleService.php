<?php

namespace App\Services;

use App\Models\ProductBundle;

class ProductBundleService
{
    public function store(array $array): ProductBundle
    {
        $productBundle = ProductBundle::create($array);

        return $productBundle;
    }

    public function update(ProductBundle $productBundle, array $array): ProductBundle
    {
        $productBundle->update($array);

        return $productBundle->refresh();
    }
}
