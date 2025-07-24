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
use Illuminate\Support\Facades\Log;

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
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return JsonResource::collection($data);
    }

    /**
     * Store a new Tenant
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
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
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
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
