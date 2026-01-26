<?php

namespace App\Listeners;

use App\Enums\NotificationEnum;
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
        if (($quoteApproval->flow !== QuoteApprovalFlowEnum::MARGIN->value || $quoteApproval->status !== QuoteApprovalStatus::PENDING->name)) {
            return;
        }

        $isMarginValid = $quoteApproval->quote->items->every(fn ($item) => $item->isWithinMarginThresholds());
        if (! $isMarginValid) {
            $quoteApproval->status = QuoteApprovalStatus::REJECTED->name;
            $quoteApproval->comments = 'Margin requirements not met.';
            $quoteApproval->saveQuietly();
            $this->quoteApprovalService->store(['quote_id' => $quoteApproval->quote_id, 'flow' => QuoteApprovalFlowEnum::INTERNAL->value]);

            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been rejected automatically.';
            $type = NotificationEnum::QuoteRejected;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id], false));

        }

        if ($isMarginValid) {
            $quoteApproval->status = QuoteApprovalStatus::APPROVED->name;
            $quoteApproval->saveQuietly();
            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::APPROVED->name]);

            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been approved automatically.';
            $type = NotificationEnum::QuoteApproved;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id], false));
        }

    }
}
