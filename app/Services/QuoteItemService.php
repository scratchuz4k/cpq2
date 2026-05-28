<?php

namespace App\Services;

use App\Models\PricePlan;
use App\Models\Product;
use App\Models\QuoteItem;

class QuoteItemService
{
    public function store(array $array): QuoteItem
    {
        if (array_key_exists('price_plan_code', $array)) {
            $id = PricePlan::where('code', $array['price_plan_code'])->value('id');
            $array['price_plan_id'] = $id;
        }

        if (array_key_exists('product_id', $array) && ! is_null($array['product_id'])) {
            $product = Product::find($array['product_id']);

            if (! array_key_exists('data', $array) && ! is_array($array['data'])) {
                $array['data'] = [];
            }

            if ($product->bundles->count() > 0) {
                $array['data']['bundle_items'] = $product->bundles->map(function ($bundle) {
                    return [
                        'product_id' => $bundle->id,
                        'name' => $bundle->name,
                    ];
                })->toArray();
            }
        }

        $quoteItem = QuoteItem::create([
            ...$array,
            'original_price' => $array['price'] ?? 0,
        ]);

        return $quoteItem;
    }

    public function update(QuoteItem $quoteItem, array $array): QuoteItem
    {
        $quoteItem->update($array);

        $quoteItem->computeTotals();

        return $quoteItem;
    }
}
