<?php

namespace App\Services;

use App\Models\ProductLink;

class ProductLinkService
{
    public function store(array $array): ProductLink
    {
        $productLink = ProductLink::create($array);

        return $productLink;
    }

    public function update(ProductLink $productLink, array $array): ProductLink
    {
        $productLink->update($array);

        return $productLink->refresh();
    }
}
