<?php

namespace App\Http\Controllers\Api\Cloudaudit;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrganisationCreateRequest;
use App\Http\Requests\OrganisationUpdateRequest;
use App\Http\Resources\Cloudaudit\OrganisationResource;
use App\Jobs\CloudauditAccountSyncJob;
use App\Models\Cloudaudit\Organisation;
use App\Services\OrganisationService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Support\Facades\DB;

class OrganisationController extends BaseController
{
    public function __construct(protected OrganisationService $service)
    {
        parent::__construct();
    }

    /**
     * List Organisations
     */
    #[ExcludeRouteFromDocs]
    public function index()
    {
        if (! config('services.cloudaudit.live_data')) {
            $mock_organisations = storage_path('mocks/organisations.json');
            if (! is_file($mock_organisations)) {
                return [];
            }

            return json_decode(file_get_contents($mock_organisations), true);
        }

        $dataProvider = new DataProvider(Organisation::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return OrganisationResource::collection($data);
    }

    /**
     * Store a new Organisation
     */
    #[ExcludeRouteFromDocs]
    public function store(OrganisationCreateRequest $request)
    {
        try {
            $organisation = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new OrganisationResource($organisation->refresh()));
    }

    /**
     * Show Organisation
     */
    #[ExcludeRouteFromDocs]
    public function show(Organisation $organisation)
    {
        //
    }

    /**
     * Update Organisation
     */
    #[ExcludeRouteFromDocs]
    public function update(OrganisationUpdateRequest $request, Organisation $organisation)
    {
        //
    }

    /**
     * Remove Organisation
     */
    #[ExcludeRouteFromDocs]
    public function destroy(Organisation $organisation)
    {
        //
    }

    /**
     * Sync Organisations
     */
    #[ExcludeRouteFromDocs]
    public function sync()
    {
        try {
            if (! config('services.cloudaudit.live_data')) {
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
