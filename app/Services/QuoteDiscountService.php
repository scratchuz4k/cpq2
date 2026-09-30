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

        $discount = QuoteDiscount::updateOrCreate([
            'quote_id' => $array['quote_id'] ?? null,
            'discountable_type' => $array['discountable_type'],
            'discountable_id' => $array['discountable_id'],
            'billing_frequency_unit' => $array['billing_frequency_unit'] ?? null,
        ], $array);

        return $discount;
    }

    public function update(QuoteDiscount $discount, array $array): QuoteDiscount
    {
        $discount->update($array);

        return $discount->refresh();
    }
}
