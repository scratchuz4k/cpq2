<?php

namespace App\Services;

use App\Enums\QuoteApprovalStatus;
use App\Enums\QuoteStatusesEnum;
use App\Models\Quote;
use App\Models\QuoteApproval;
use Illuminate\Support\Facades\Auth;

class QuoteApprovalService
{
    public function store(array $array): QuoteApproval
    {
        $array['status'] = QuoteApprovalStatus::Pending->value;
        $array['flow'] = array_key_exists('flow', $array) ? $array['flow'] : get_tenant_config('quote_approval_flow');
        $quote = Quote::find($array['quote_id']);

        if (in_array($quote->status, [QuoteStatusesEnum::VALIDATED->name, QuoteStatusesEnum::COMPLETED->name])) {
            throw new \Exception('Quote is already validated or completed.');
        }

        $hasActiveFlow = $quote->approvals->some(fn ($item) => $item->status == QuoteApprovalStatus::Pending->value);
        if ($hasActiveFlow) {
            throw new \Exception('An active approval flow already exists for this quote.');
        }
        $quote->update(['status' => QuoteStatusesEnum::NEED_APPROVAL->name]);
        $quoteApproval = QuoteApproval::create($array);

        return $quoteApproval;
    }

    public function update(QuoteApproval $quoteApproval, array $array): QuoteApproval
    {
        $array['approver_id'] = Auth::id();

        if (in_array($quoteApproval->status, [QuoteApprovalStatus::Approved->value, QuoteApprovalStatus::Rejected->value])) {
            throw new \Exception('This approval has already been finalized.');
        }

        $quoteApproval->update($array);

        return $quoteApproval;
    }
}
