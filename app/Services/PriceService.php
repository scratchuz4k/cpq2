<?php

namespace App\Services;

use App\Models\Price;

class PriceService
{
    public function store(array $array): Price
    {
        $price = Price::create($array);

        return $price;
    }

    public function update(Price $price, array $array): Price
    {
        $price->update($array);

        return $price;
    }
}
