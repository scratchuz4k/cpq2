<?php

namespace App\Services;

use App\Events\QuoteStatusEvent;
use App\Models\PricePlan;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class QuoteItemService
{

    public function store(array $array): QuoteItem
    {
        if (array_key_exists("price_plan_code", $array)) {
            $id = PricePlan::where('code', $array["price_plan_code"])->value("id");
            $array['price_plan_id'] = $id;
        }

        $quoteItem = QuoteItem::create([
            ...$array,
            'tax_order_item' => 20,
            'original_price' => isset($array['price']) ? $array['price'] : 0,
        ]);

        if (array_key_exists('children', $array)) {
            foreach ($array['children'] as $child) {
                if (array_key_exists("price_plan_code", $child)) {
                    $id = PricePlan::where('code', $child["price_plan_code"])->value("id");
                    $child['price_plan_id'] = $id;
                }

                $childItem = QuoteItem::create([
                    ...$array,
                    ...$child,
                    'parent_id' => $quoteItem->id,
                    'tax_order_item' => 20,
                    'original_price' => isset($child['price']) ? $child['price'] : 0,
                ]);

                $childItem->computeTotals();
            }
        }

        $quoteItem->computeTotals();
        return $quoteItem;
    }

    public function update(QuoteItem $quoteItem, array $array): QuoteItem
    {
        $quoteItem->update($array);

        $quoteItem->computeTotals();
        return $quoteItem;
    }
}
