<?php

namespace App\Listeners;

use App\Models\Quote;
use App\Services\OrderService;
use App\Services\QuoteService;
use App\Enums\OrderStatusesEnum;
use App\Enums\QuoteStatusesEnum;
use App\Events\OrderStatusEvent;
use App\Events\QuoteStatusEvent;
use App\Services\OrderItemService;
use App\Services\QuoteItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusRejectedListener
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
                $this->quoteService->update($order->quote,  ['status' => $order->status]);
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            $order->status = OrderStatusesEnum::REGISTRY->name;
            $order->save();
        }
    }
}
