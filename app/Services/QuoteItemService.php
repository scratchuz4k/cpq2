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
        return DB::transaction(function () use ($array) {

            if (array_key_exists("price_plan_code", $array)) {
                $id = PricePlan::where('code', $array["price_plan_code"])->value("id");
                $array['price_plan_id'] = $id;
            }

            $quoteItem = QuoteItem::create([
                ...$array,
                'tax_order_item' => 20,
                'original_price' => $array['price'],
            ]);

            $quoteItem->computeTotals();
            $quoteItem->quote->computeTotals();
            event(QuoteStatusEvent::class, $quoteItem->quote);
            return $quoteItem;
        });
    }

    public function update(QuoteItem $quoteItem, array $array): QuoteItem
    {
        return DB::transaction(function () use ($array, $quoteItem) {
            $quoteItem->update($array);

            $quoteItem->computeTotals();
            $quoteItem->quote->computeTotals();
            event(QuoteStatusEvent::class, $quoteItem->quote);
            return $quoteItem;
        });
    }
}
