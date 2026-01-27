<?php

namespace App\Services;

use App\Models\QuoteDiscount;

class QuoteDiscountService
{
    public function store(array $array): QuoteDiscount
    {
        $discount = QuoteDiscount::create($array);

        return $discount;
    }

    public function update(QuoteDiscount $discount, array $array): QuoteDiscount
    {
        $discount->update($array);

        if (array_key_exists('product_id', $array)) {
            $discount->products()->toggle($array['product_id']);
        }

        return $discount->refresh();
    }
}
