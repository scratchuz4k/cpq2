<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShareLinkCreateRequest;
use App\Http\Resources\SharedLinkResource;
use App\Models\Quote;
use App\Models\SharedLink;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        'quotes' => Quote::class,
    ];

    /**List of SharedLinks
     * @response AnonymousResourceCollection<LengthAwarePaginator<RegionResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index()
    {
        //
    }

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
     * Show SharedLink
     */
    #[ExcludeRouteFromDocs]
    public function show(SharedLink $sharedLink)
    {
        //
    }

    /**
     * Update SharedLink
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, SharedLink $sharedLink)
    {
        //
    }

    /**
     * Remove Shared Link
     */
    public function destroy(SharedLink $sharedLink)
    {
        $sharedLink->delete();
    }
}
