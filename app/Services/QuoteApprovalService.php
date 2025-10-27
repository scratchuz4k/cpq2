<?php

namespace App\Services;

use App\Models\QuoteApproval;
use Illuminate\Support\Facades\Auth;

class QuoteApprovalService
{
    public function store(array $array): QuoteApproval
    {
        $array['status'] = 'pending';
        $array['flow'] = get_tenant_config('quote_approval_flow');

        $quoteApproval = QuoteApproval::create($array);

        return $quoteApproval;
    }

    public function update(QuoteApproval $quoteApproval, array $array): QuoteApproval
    {
        $array['approver_id'] = Auth::id();
        $quoteApproval->update($array);

        return $quoteApproval;
    }
}
