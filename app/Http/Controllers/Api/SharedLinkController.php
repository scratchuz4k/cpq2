<?php

namespace App\Http\Controllers\Api;

use App\Models\Quote;
use App\Models\SharedLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShareLinkCreateRequest;
use App\Http\Resources\SharedLinkResource;
use Illuminate\Support\Facades\Auth;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        'quotes' => Quote::class
    ];

    /**
     * List of Shared Links
     */
    public function store(ShareLinkCreateRequest $request)
    {
        $sharedLink = SharedLink::create([
            ...$request->all(),
            'tenant_id' => Auth::user()->tenant_id,
            'shareable_type' => $this->sharedModels[$request->input('shareable_type')],
            'expires_at' => $request->input('expires_at', null), // Expire after 7 days
        ]);

        // Optionally redirect or return the shareable resource
        return new SharedLinkResource($sharedLink);
    }

    /**
     * Remove Shared Link
     */
    public function destroy(SharedLink $sharedLink)
    {
        $sharedLink->delete();
    }
}
