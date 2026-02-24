<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteApproval;
use App\Models\SharedLink;
use Exception;
use Illuminate\Support\Facades\Auth;

class SharedLinkService
{
    private $sharedModels = [
        'quotes' => Quote::class,
        'quote_approvals' => QuoteApproval::class,
    ];

    public function store(array $array): SharedLink
    {
        $shareable = array_key_exists('shareable_type', $array) ? $this->sharedModels[$array['shareable_type']] : null;
        if (! $shareable) {
            throw new Exception('The shareable type is invalid!');
        }

        $sharedLink = SharedLink::create([
            ...$array,
            'tenant_id' => Auth::user()->tenant_id,
            'shareable_type' => $shareable,
            'expires_at' => array_key_exists('expires_at', $array) ? $array['expires_at'] : null,
        ]);

        return $sharedLink;
    }
}
