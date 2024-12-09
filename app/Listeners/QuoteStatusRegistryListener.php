<?php

namespace App\Listeners;

use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteStatusEvent;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\OrderItemService;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteStatusRegistryListener
{

    protected OrderService $orderService;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->orderService = new OrderService(new OrderItemService);
    }

    /**
     * Handle the event.
     */
    public function handle(QuoteStatusEvent $event): void
    {
        $quote = $event->quote;
        if (!($quote->status === QuoteStatusesEnum::REGISTRY->name)) {
            return;
        }

        if ($quote->items()->count() <= 0) {
            throw new \Exception("Quote does not meet the requirements to create an Order.");
        }

        try {
            $order = DB::transaction(function () use ($quote) {
                $quote = (new QuoteResource($quote->load(['items', 'items.children'])))->resolve();
                $this->orderService->store([...$quote, 'user_id' => null, 'quote_id' => $quote['id']]);
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
