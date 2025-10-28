<?php

namespace App\Listeners;

use App\Enums\QuoteApprovalFlowEnum;
use App\Enums\QuoteApprovalStatus;
use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteApprovalEvent;
use App\Services\QuoteApprovalService;
use App\Services\QuoteItemService;
use App\Services\QuoteService;

class QuoteApprovalMarginListener
{
    protected QuoteService $quoteService;

    protected QuoteApprovalService $quoteApprovalService;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->quoteService = new QuoteService(new QuoteItemService);
        $this->quoteApprovalService = new QuoteApprovalService;
    }

    /**
     * Handle the event.
     */
    public function handle(QuoteApprovalEvent $event): void
    {
        $quoteApproval = $event->quoteApproval;
        if (($quoteApproval->flow !== QuoteApprovalFlowEnum::Margin->value || $quoteApproval->status !== QuoteApprovalStatus::Pending->value)) {
            return;
        }

        $isMarginValid = $quoteApproval->quote->items->every(fn ($item) => $item->isWithinMarginThresholds());
        if (! $isMarginValid) {
            $quoteApproval->status = QuoteApprovalStatus::Rejected->value;
            $quoteApproval->comments = 'Margin requirements not met.';
            $quoteApproval->saveQuietly();
            $this->quoteApprovalService->store(['quote_id' => $quoteApproval->quote_id, 'flow' => QuoteApprovalFlowEnum::Internal->value]);

            return;
        }

        if ($isMarginValid) {
            $quoteApproval->status = QuoteApprovalStatus::Approved->value;
            $quoteApproval->saveQuietly();
            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::VALIDATED->name]);
        }

    }
}
