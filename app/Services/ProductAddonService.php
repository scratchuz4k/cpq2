<?php

namespace App\Services;

use App\Models\ProductAddon;

class ProductAddonService
{
    public function store(array $array): ProductAddon
    {
        $productAddon = ProductAddon::create($array);

        return $productAddon;
    }

    public function update(ProductAddon $productAddon, array $array): ProductAddon
    {
        $productAddon->update($array);

        return $productAddon->refresh();
    }
}
