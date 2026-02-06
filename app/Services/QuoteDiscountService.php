<?php

namespace App\Services;

use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\QuoteDiscount;

class QuoteDiscountService
{
    public function store(array $array): QuoteDiscount
    {
        $entities = [
            'price_plans' => PricePlan::class,
            'quote' => Quote::class,
        ];

        if (array_key_exists('entity', $array) && array_key_exists($array['entity'], $entities)) {
            $array['discountable_type'] = $entities[$array['entity']];
            $array['discountable_id'] = $array['entity_id'];
        }

        $discount = QuoteDiscount::create($array);

        return $discount;
    }

    public function update(QuoteDiscount $discount, array $array): QuoteDiscount
    {
        $discount->update($array);

        return $discount->refresh();
    }
}
