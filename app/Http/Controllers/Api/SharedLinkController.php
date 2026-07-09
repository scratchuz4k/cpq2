<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ShareLinkCreateRequest;
use App\Http\Resources\SharedLinkResource;
use App\Models\SharedLink;
use App\Services\SharedLinkService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharedLinkController extends BaseController
{
    public function __construct(protected SharedLinkService $service)
    {
        parent::__construct();
    }

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
    #[ExcludeRouteFromDocs]
    public function store(ShareLinkCreateRequest $request)
    {

        try {
            $sharedLink = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

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
    #[ExcludeRouteFromDocs]
    public function destroy(SharedLink $sharedLink)
    {
        $sharedLink->delete();
    }
}
