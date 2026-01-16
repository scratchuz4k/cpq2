<?php

namespace App\Http\Controllers\Api\Cloudaudit;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrganizationCreateRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Http\Resources\Cloudaudit\OrganizationResource;
use App\Jobs\CloudauditAccountSyncJob;
use App\Models\Cloudaudit\Organization;
use App\Services\OrganizationService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Support\Facades\DB;

class OrganizationController extends BaseController
{
    public function __construct(protected OrganizationService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    #[ExcludeRouteFromDocs]
    public function index()
    {
        if (! config('external_sources.cloudaudit.live_data')) {
            $mock_organizations = storage_path('mocks/organizations.json');
            if (! is_file($mock_organizations)) {
                return [];
            }

            return json_decode(file_get_contents($mock_organizations), true);
        }

        $dataProvider = new DataProvider(Organization::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return OrganizationResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizationCreateRequest $request)
    {
        try {
            $organization = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new OrganizationResource($organization->refresh()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrganizationUpdateRequest $request, Organization $organization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    #[ExcludeRouteFromDocs]
    public function sync()
    {
        try {
            if (! config('external_sources.cloudaudit.live_data')) {
                return response()->json([], 200);
            }
            $job = new CloudauditAccountSyncJob;
            $job->runSync();
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json([], 200);
    }
}
