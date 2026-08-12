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
            $bundleItems = $this->resolveBundleItems($array['product_id'], $array['quantity'] ?? 1);
            if ($bundleItems) {
                $data['bundle_items'] = $bundleItems;
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
        if (
            array_key_exists('quantity', $array)
            && $quoteItem->product_id
            && ($quoteItem->data?->type ?? null) === 'main_product'
        ) {
            $bundleItems = $this->resolveBundleItems($quoteItem->product_id, $array['quantity']);
            if ($bundleItems) {
                $array['data'] = [...(array) $quoteItem->data, 'bundle_items' => $bundleItems];
            }
        }

        $quoteItem->update($array);

        $quoteItem->computeTotals();

        return $quoteItem;
    }

    /**
     * Build the bundle_items metadata for a main product, scaling each bundled
     * item's quantity by the number of the parent product being added.
     */
    private function resolveBundleItems(int $productId, int $quantity): array
    {
        $product = Product::find($productId);

        if (! $product || $product->bundlesChildren->count() === 0) {
            return [];
        }

        return $product->bundlesChildren
            ->map(fn ($bundle) => [
                'product_id' => $bundle->id,
                'name' => $bundle->name,
                'quantity' => ($bundle->pivot->quantity ?? 1) * $quantity,
            ])
            ->toArray();
    }
}
