<?php

namespace App\Listeners;

use App\Enums\OrderStatusesEnum;
use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteStatusEvent;
use App\Models\Order;
use App\Models\Quote;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Illuminate\Events\queueable;

class QuoteStatusListener
{
    /**
     * Create the event listener.
     */
    public function __construct( protected OrderService $orderService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Quote $quote): void
    {
        $quote->status = $this->resolveStatus($quote);
        $quote->save();

        switch ($quote->status) {
            case QuoteStatusesEnum::REGISTRY->name:
            $this->createOrder($quote->load(['items']));

            break;
        }
    }

    private function resolveStatus(Quote $quote)
    {
        if ($quote->status === QuoteStatusesEnum::REGISTRY->name) {
            if($quote->items()->count() <= 0) {
                throw new \Exception("Quote does not meet the requirements to create an Order.");
            }
            return QuoteStatusesEnum::REGISTRY->name;
        }

        if ($quote->items->count()) {
            return QuoteStatusesEnum::QUOTE->name;
        }

        if (!empty($quote->address_data)) {
            return QuoteStatusesEnum::PRICE->name;
        }

        return QuoteStatusesEnum::CONFIGURE->name;

    }

    private function createOrder(Quote $quote){
        if($quote->items()->count() <= 0) {
            throw new \Exception("Quote does not meet the requirements to create an Order.");
        }

        try {
            $order = DB::transaction(function () use ($quote) {
                $this->orderService->store([...$quote->toArray(), 'user_id' => null, 'quote_id' => $quote->id]);
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            $quote->status = QuoteStatusesEnum::PRICE->name;
            $quote->save();
        }
    }
}

