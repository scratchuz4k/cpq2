<?php

namespace App\Listeners;

use App\Enums\NotificationEnum;
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
        if ($quoteApproval->status === QuoteApprovalStatus::PENDING->value) {
            return;
        }

        if (
            $quoteApproval->flow === QuoteApprovalFlowEnum::INTERNAL->value
            && $quoteApproval->status === QuoteApprovalStatus::REJECTED->value
        ) {

            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::REJECTED->name]);
            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been rejected.';
            $type = NotificationEnum::QuoteRejected;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id]), false);
        }

        if (
            $quoteApproval->flow === QuoteApprovalFlowEnum::INTERNAL->value
            && $quoteApproval->status === QuoteApprovalStatus::APPROVED->value
        ) {
            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::APPROVED->name]);
            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been approved.';
            $type = NotificationEnum::QuoteApproved;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id]), false);
        }

    }
}
