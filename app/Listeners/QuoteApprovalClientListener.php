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

class QuoteApprovalClientListener
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
        if ($quoteApproval->status === QuoteApprovalStatus::PENDING->name) {
            return;
        }

        if (
            $quoteApproval->flow === QuoteApprovalFlowEnum::CLIENT->value
            && $quoteApproval->status === QuoteApprovalStatus::REJECTED->name
        ) {

            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::REJECTED->name]);
            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been rejected by the client.';
            $type = NotificationEnum::QuoteRejected;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id], false));
        }

        if (
            $quoteApproval->flow === QuoteApprovalFlowEnum::CLIENT->value
            && $quoteApproval->status === QuoteApprovalStatus::APPROVED->name
        ) {

            $this->quoteService->update($quoteApproval->quote, ['status' => QuoteStatusesEnum::APPROVED->name]);
            $description = 'Your quote #'.$quoteApproval->quote->id.' for the account '.$quoteApproval->quote->account->name.' has been approved by the client.';
            $type = NotificationEnum::QuoteApproved;
            createNotification($type, $quoteApproval->quote->user, $description, route('quotes.show', ['quote' => $quoteApproval->quote->id], false));
        }

    }
}
