<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Services\TenantService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class TenantController extends BaseController
{
    protected $model = Tenant::class;

    public function __construct(protected TenantService $tenantService)
    {
        parent::__construct();
    }

    /**
     * List of Tenants
     */
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(Tenant::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return JsonResource::collection($data);
    }

    /**
     * Store a new Tenant
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        try {
            $tenant = DB::transaction(fn () => $this->tenantService->create($request->all()));
        } catch (\Exception $e) {
            return handleException($e);
        }

        return JsonResource::make($tenant)->resolve();
    }

    /**
     * Show Tenant
     */
    #[ExcludeRouteFromDocs]
    public function show(string $id)
    {
        //
    }

    /**
     * Update Tenant
     */
    public function update(TenantUpdateRequest $request, Tenant $tenant)
    {
        try {
            $tenant = DB::transaction(function () use ($tenant, $request) {
                $tenant = $this->tenantService->update($tenant, $request->all());

                return $tenant;
            });
        } catch (\Exception $e) {
            return handleException($e);
        }

        return response()->json((new TenantResource($tenant))->resolve());
    }

    /**
     * Remove Tenant
     */
    #[ExcludeRouteFromDocs]
    public function destroy(string $id)
    {
        //
    }
}
