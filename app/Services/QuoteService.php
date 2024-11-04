<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class QuoteService
{

    public function __construct(protected QuoteItemService $quoteItemService) {}
    public function store(array $array): Quote
    {
        return DB::transaction(function () use ($array) {
            $quote = Quote::create($array);

            if (array_key_exists('items', $array)) {
                foreach ($array['items'] as $quote_item) {
                    $quote_item['quote_id'] = $quote->id;
                    $this->quoteItemService->store($quote_item);
                }
            }

            $quote->update(['status' => get_quote_status($quote->refresh())]);
            return $quote;
        }, 3);
    }

    public function update(Quote $quote, array $array): Quote
    {
        return DB::transaction(function () use ($quote, $array) {

            $quote->update($array);
            if (array_key_exists('items', $array)) {
                foreach ($array['items'] as $quote_item) {
                    $quote_item['quote_id'] = $quote->id;
                    if ($temp = QuoteItem::find($quote_item['id'])) {
                        $this->quoteItemService->update($temp, $quote_item);
                        continue;
                    }
                    $this->quoteItemService->store($quote_item);
                }
            }

            $quote->update(['status' => get_quote_status($quote->refresh())]);
            return $quote;
        }, 3);
    }
}
