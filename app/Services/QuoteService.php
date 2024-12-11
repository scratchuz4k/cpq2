<?php

namespace App\Services;

use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\QuoteItem;

class QuoteService
{
    protected $discounsEntities = [
        'price_plans' => PricePlan::class
    ];
    public function __construct(protected QuoteItemService $quoteItemService) {}
    public function store(array $array): Quote
    {
        $quote = Quote::create($array);
        return $quote;
    }

    public function update(Quote $quote, array $array): Quote
    {
        $quote->update($array);

        #TODO: MOVE THIS LOGIC TO A SPECIFIC CONTROLLER
        if (array_key_exists('discounts', $array)) {
            foreach ($array['discounts'] as $key => $discount) {
                if (!array_key_exists('entity', $discount)) {
                    continue;
                }

                $model = $this->discounsEntities[$discount['entity']];
                $object = app($model)::where('code', $discount['code'])->first();

                if (!$object) {
                    continue;
                }

                $quote->discounts()->updateOrCreate([
                    "discountable_type" => $model,
                    "discountable_id" => $object->id,
                ], [
                    "discount" =>  $discount['value']
                ]);
            }
        }

        if (array_key_exists('items', $array)) {
            $olderItems = collect($quote->items);
            foreach ($array['items'] as $quote_item) {
                if (array_key_exists('id', $quote_item) && !is_null($quote_item['id'])) {
                    $temp = QuoteItem::find($quote_item['id']);
                    $this->quoteItemService->update($temp, [...$quote_item, 'quote_id' =>  $quote->id]);

                    $olderItems = $olderItems->reject(function ($temp) use ($quote_item) {
                        $id = array_key_exists('parent_id', $quote_item) && $quote_item['parent_id'] ? $quote_item['parent_id'] : $quote_item['id'];
                        return $temp->id === $id;
                    });
                } else {
                    $this->quoteItemService->store([...$quote_item, 'quote_id' =>  $quote->id]);
                }
            }
            foreach ($olderItems as $temp) {
                $temp->delete();
            }
        }

        $quote->update($array);
        return $quote->refresh();
    }
}
