<?php

namespace App\Services;

use App\Models\Price;
use Illuminate\Support\Facades\DB;

class PriceService
{
    public function store(array $array): Price
    {
        return DB::transaction(function () use ($array) {
            $price = Price::create([
                ...$array,
                "tenant_id" => session()->get('tenant_id'),
            ]);


            return $price;
        });
    }

    public function update(Price $price, array $array): Price
    {
        return DB::transaction(function () use ($price, $array) {
            $price->update(
                [
                    ...$array,
                    'tenant_id' => session()->get('tenant_id'),
                ]
            );

            return $price;
        });
    }
}
