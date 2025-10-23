<?php

namespace App\Services;

use App\Models\QuoteApproval;

class QuoteApprovalService
{
    public function store(array $array): QuoteApproval
    {
        $quoteApproval = QuoteApproval::create($array);

        return $quoteApproval;
    }

    public function update(QuoteApproval $quoteApproval, array $array): QuoteApproval
    {
        $quoteApproval->update($array);

        return $quoteApproval;
    }
}
