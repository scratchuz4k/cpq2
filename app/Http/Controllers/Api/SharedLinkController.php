<?php

namespace App\Http\Controllers\Api;

use App\Models\Quote;
use App\Models\SharedLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShareLinkCreateRequest;
use App\Http\Resources\SharedLinkResource;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        'quotes' => Quote::class
    ];

    public function store(ShareLinkCreateRequest $request)
    {
        $sharedLink = SharedLink::create([
            ...$request->all(),
            'shareable_type' => $this->sharedModels[$request->input('shareable_type')],
            'expires_at' => $request->input('expires_at', now()->addDays(7)), // Expire after 7 days
        ]);

        // Optionally redirect or return the shareable resource
        return new SharedLinkResource($sharedLink);
    }

    public function destroy(SharedLink $sharedLink)
    {
        $sharedLink->delete();
    }
}
