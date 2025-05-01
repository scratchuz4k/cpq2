<?php

namespace App\Services;

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
            'original_price' => isset($array['price']) ? $array['price'] : 0,
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
