<?php

namespace App\Services;

use App\Enums\QuoteApprovalStatus;
use App\Enums\QuoteStatusesEnum;
use App\Models\Quote;
use App\Models\QuoteApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QuoteApprovalService
{
    public function store(array $array): QuoteApproval
    {
        $array['status'] = QuoteApprovalStatus::Pending->value;
        $array['flow'] = array_key_exists('flow', $array) ? $array['flow'] : get_tenant_config('quote_approval_flow');
        $quote = Quote::find($array['quote_id']);
        $quote->update(['status' => QuoteStatusesEnum::NEED_APPROVAL->name]);
        $quoteApproval = QuoteApproval::create($array);

        return $quoteApproval;
    }

    public function update(QuoteApproval $quoteApproval, array $array): QuoteApproval
    {
        $array['approver_id'] = Auth::id();

        $quoteApproval->update($array);

        return $quoteApproval;
    }

    public function validateStore(Quote $quote): ?JsonResponse
    {
        if ($quote->status != QuoteStatusesEnum::QUOTE->name) {
            return response()->json(['message' => 'Quote is not ready for approval.'], 422);
        }

        if (in_array($quote->status, [QuoteStatusesEnum::VALIDATED->name, QuoteStatusesEnum::COMPLETED->name])) {
            return response()->json(['message' => 'Quote is already validated or completed.'], 422);
        }

        $hasActiveFlow = $quote->approvals->some(fn ($item) => $item->status == QuoteApprovalStatus::Pending->value);
        if ($hasActiveFlow) {
            return response()->json(['message' => 'An active approval flow already exists for this quote.'], 422);
        }

        return null;
    }

    public function validateUpdate(QuoteApproval $quoteApproval): ?JsonResponse
    {
        if (in_array($quoteApproval->status, [QuoteApprovalStatus::Approved->value, QuoteApprovalStatus::Rejected->value])) {
            return response()->json(['message' => 'This approval has already been finalized.'], 422);
        }

        return null;
    }
}
