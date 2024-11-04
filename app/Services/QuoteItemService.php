<?php

namespace App\Services;

use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class QuoteItemService
{

    public function store(array $array): QuoteItem
    {
        return DB::transaction(function () use ($array) {
            $quoteItem = QuoteItem::create($array);

            $quoteItem->quote->update(['status' => get_quote_status($quoteItem->quote)]);
            return $quoteItem;
        });
    }

    public function update(QuoteItem $quoteItem, array $array): QuoteItem
    {
        return DB::transaction(function () use ($array, $quoteItem) {
            $quoteItem->update($array);

            $quoteItem->quote->update(['status' => get_quote_status($quoteItem->quote)]);
            return $quoteItem;
        });
    }
}
