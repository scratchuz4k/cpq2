<?php

namespace App\Services;

use App\Models\PricePlan;
use App\Models\Quote;

class QuoteService
{
    protected $discounsEntities = [
        'price_plans' => PricePlan::class,
    ];

    public function __construct(protected QuoteItemService $quoteItemService) {}

    public function store(array $array): Quote
    {
        $quote = Quote::create($array);

        return $quote;
    }

    public function update(Quote $quote, array $array): Quote
    {
        //TODO: MOVE THIS LOGIC TO A SPECIFIC CONTROLLER
        if (array_key_exists('discounts', $array)) {
            foreach ($array['discounts'] as $key => $discount) {
                if (! array_key_exists('entity', $discount)) {
                    continue;
                }

                $model = $this->discounsEntities[$discount['entity']];
                $object = app($model)::where('code', $discount['code'])->first();

                if (! $object) {
                    continue;
                }

                $quote->discounts()->updateOrCreate([
                    'discountable_type' => $model,
                    'discountable_id' => $object->id,
                ], [
                    'discount' => $discount['value'],
                ]);
            }
        }

        $quote->update($array);

        return $quote->refresh();
    }
}
