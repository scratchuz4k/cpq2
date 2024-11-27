<?php

namespace App\Listeners;

use App\Enums\QuoteStatusesEnum;
use App\Events\OrderStatusEvent;
use App\Events\QuoteStatusEvent;
use App\Models\Quote;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteStatusRegistryListener
{
    /**
     * Create the event listener.
     */
    public function __construct(protected OrderService $orderService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QuoteStatusEvent $quoteStatusUpdate): void
    {
        $quote = $quoteStatusUpdate->quote;
        if (!($quoteStatusUpdate->newStatus === QuoteStatusesEnum::REGISTRY->name)) {
            return;
        }

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
