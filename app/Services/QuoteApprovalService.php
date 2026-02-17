<?php

namespace App\Services;

use App\Enums\EmailTemplatesEnum;
use App\Enums\QuoteApprovalFlowEnum;
use App\Enums\QuoteApprovalStatus;
use App\Enums\QuoteStatusesEnum;
use App\Helpers\DynamicEmail;
use App\Models\Quote;
use App\Models\QuoteApproval;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class QuoteApprovalService
{
    protected $sharedLinkService;

    public function __construct()
    {
        $this->sharedLinkService = new SharedLinkService;
    }

    public function store(array $array): QuoteApproval
    {
        $array['status'] = QuoteApprovalStatus::PENDING->name;
        $array['flow'] = array_key_exists('flow', $array) ? $array['flow'] : get_tenant_config('quote_approval_flow');
        $quote = Quote::find($array['quote_id']);
        $quote->update(['status' => QuoteStatusesEnum::PENDING->name]);
        $quoteApproval = QuoteApproval::create($array);

        if ($quoteApproval->flow == QuoteApprovalFlowEnum::CLIENT->value) {
            $password = Str::random(8);
            $link = $this->sharedLinkService->store([
                'shareable_type' => 'quote_approvals',
                'shareable_id' => $quoteApproval->id,
                'password' => $password,
            ]);

            $contact = $quote->account->contacts->first();
            if (! $contact || ! $contact->email) {
                throw new Exception('Account doesnt have a valid contact!');
            }

            Mail::queue(
                new DynamicEmail(
                    user: Auth::user(),
                    tenant: Auth::user()->currentTenant,
                    templateEnum: EmailTemplatesEnum::QUOTE_CLIENT_REVIEW,
                    parameters: [
                        'quote_link' => route('share_link', ['token' => $link->token]),
                        'account_name' => $quote->account->name,
                        'quote_number' => $quote->id,
                        'quote_password' => $password,
                    ],
                    overrides: [
                        'to' => $contact->email,
                    ],
                )
            );
        }

        return $quoteApproval;
    }

    public function update(QuoteApproval $quoteApproval, array $array): QuoteApproval
    {
        if (! array_key_exists('approver_id', $array)) {
            $array['approver_id'] = Auth::id();
        }

        $quoteApproval->update($array);

        return $quoteApproval;
    }

    public function validateStore(Quote $quote): ?JsonResponse
    {
        if ($quote->status != QuoteStatusesEnum::QUOTE->name) {
            return response()->json(['message' => 'Quote is not ready for approval.'], 422);
        }

        if (in_array($quote->status, [QuoteStatusesEnum::APPROVED->name, QuoteStatusesEnum::COMPLETED->name])) {
            return response()->json(['message' => 'Quote is already approved or completed.'], 422);
        }

        $hasActiveFlow = $quote->approvals->some(fn ($item) => $item->status == QuoteApprovalStatus::PENDING->name);
        if ($hasActiveFlow) {
            return response()->json(['message' => 'An active approval flow already exists for this quote.'], 422);
        }

        return null;
    }

    public function validateUpdate(QuoteApproval $quoteApproval): ?JsonResponse
    {
        if (in_array($quoteApproval->status, [QuoteApprovalStatus::APPROVED->name, QuoteApprovalStatus::REJECTED->name])) {
            return response()->json(['message' => 'This approval has already been finalized.'], 422);
        }

        if ($quoteApproval->approver && $quoteApproval->approver->id != Auth::id()) {
            return response()->json(['message' => 'This Quote Approval is assigned to another user'], 422);
        }

        return null;
    }
}
