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

        $data = is_array($array['data'] ?? null) ? $array['data'] : [];

        if (! empty($array['product_id']) && ($data['type'] ?? null) === 'main_product') {
            $product = Product::find($array['product_id']);
            if ($product->bundles->count() > 0) {
                $data['bundle_items'] = $product->bundles
                    ->map(fn ($bundle) => ['product_id' => $bundle->id, 'name' => $bundle->name, 'quantity' => 1])
                    ->toArray();
            }
        }

        $quoteItem = QuoteItem::create([
            ...$array,
            'original_price' => $array['price'] ?? 0,
            'data' => $data,
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
