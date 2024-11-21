<?php

namespace App\Listeners;

use App\Enums\OrderStatusesEnum;
use App\Enums\QuoteStatusesEnum;
use App\Models\Order;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Illuminate\Events\queueable;
use function PHPUnit\Framework\isEmpty;

class OrderStatusListener
{
    /**
     * Create the event listener.
     */
    public function __construct( protected QuoteService $quoteService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Order $order): void
    {
        $order->status = $this->resolveStatus($order);
        $order->save();

        switch ($order->status) {
            case OrderStatusesEnum::REJECTED->name || OrderStatusesEnum::ORDERED->name:
                $this->updateQuoteStatus($order->load('quote'));
                break;
        }
    }

    private function resolveStatus(Order $order)
    {
        if ($order->status === OrderStatusesEnum::REGISTRY->name) {
            if($order->items()->count() <= 0) {
                throw new \Exception("Quote does not meet the requirements to create an Order.");
            }
            return OrderStatusesEnum::REGISTRY->name;
        }

        if ($order->status === OrderStatusesEnum::REJECTED->name) {
            return OrderStatusesEnum::REJECTED->name;
        }

        return OrderStatusesEnum::ORDERED->name;
    }

    public function updateQuoteStatus(Order $order): void
    {
        try {
            $quote = DB::transaction(function () use ($order){
                $this->quoteService->update($order->quote, ['status' => $order->status]);
            });
        } catch (\Exception $exception) {
            if (!env('APP_DEBUG')) {
                Log::error($exception->getMessage());
            }
            $order->status = OrderStatusesEnum::REGISTRY->name;
            $order->save();
        }
    }
}

