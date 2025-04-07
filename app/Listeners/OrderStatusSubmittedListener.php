<?php

namespace App\Listeners;

use App\Enums\OrderStatusesEnum;
use App\Events\OrderStatusEvent;
use App\Services\QuoteItemService;
use App\Services\QuoteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusSubmittedListener
{
    protected QuoteService $quoteService;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->quoteService = new QuoteService(new QuoteItemService);
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusEvent $event): void
    {
        $order = $event->order;

        try {
            $quote = DB::transaction(function () use ($order) {
                $this->quoteService->update($order->quote, ['status' => $order->status]);
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            $order->status = OrderStatusesEnum::REGISTRY->name;
            $order->save();
        }
    }
}
