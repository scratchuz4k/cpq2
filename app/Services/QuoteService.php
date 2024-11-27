<?php

namespace App\Services;

use App\Events\QuoteStatusEvent;
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

            event(new QuoteStatusEvent($quote, $quote->status));

            foreach ($quote->items as $quote_item) {
                $quote_item->computeTotals();
            }
            $quote->computeTotals();

            return $quote->refresh();
        }, 3);
    }

    public function update(Quote $quote, array $array): Quote
    {
        return DB::transaction(function () use ($quote, $array) {
            $quote->update($array);
            if (array_key_exists('items', $array)) {
                $olderItems = collect($quote->items);
                foreach ($array['items'] as $quote_item) {
                    if (array_key_exists('id', $quote_item) && !is_null($quote_item['id'])) {
                        $temp = QuoteItem::find($quote_item['id']);
                        $this->quoteItemService->update($temp, [...$quote_item, 'quote_id' =>  $quote->id]);

                        $olderItems = $olderItems->reject(function ($temp) use ($quote_item) {
                            return $temp->id === $quote_item['id'];
                        });
                    } else {
                        $this->quoteItemService->store([...$quote_item, 'quote_id' =>  $quote->id]);
                    }
                }
                foreach ($olderItems as $temp) {
                    $temp->delete();
                }
            }



            foreach ($quote->items as $quote_item) {
                $quote_item->computeTotals();
            }
            $quote->computeTotals();

            event(QuoteStatusEvent::class, $quote->status);
            return $quote->refresh();
        }, 3);
    }
}
