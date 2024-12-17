<?php

namespace App\Services;

use App\Models\Price;
use Illuminate\Support\Facades\DB;

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
