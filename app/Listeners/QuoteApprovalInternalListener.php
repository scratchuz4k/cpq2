<?php

namespace App\Listeners;

use App\Enums\QuoteApprovalFlowEnum;
use App\Enums\QuoteApprovalStatus;
use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteApprovalEvent;
use App\Services\QuoteItemService;
use App\Services\QuoteService;

class QuoteApprovalInternalListener
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
    public function handle(QuoteApprovalEvent $event): void
    {
        $quoteApproval = $event->quoteApproval;

        if ($quoteApproval->flow === QuoteApprovalFlowEnum::INTERNAL
            ->value && $quoteApproval->status === QuoteApprovalStatus::REJECTED->value) {
            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::QUOTE->name]);

            return;
        }

        if ($quoteApproval->flow === QuoteApprovalFlowEnum::INTERNAL
            ->value && $quoteApproval->status === QuoteApprovalStatus::APPROVED->value) {
            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::VALIDATED->name]);

            return;
        }

    }
}
