<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\SolutionTypeCreateRequest;
use App\Http\Requests\SolutionTypeUpdateRequest;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\SolutionType;
use App\Models\SolutionTypeTenant;
use App\Services\SolutionTypeService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolutionTypeController extends BaseController
{
    protected $model = SolutionType::class;

    public function __construct(protected SolutionTypeService $service)
    {
        parent::__construct();
    }

    /**
     * List of Solution Types
     */
    public function index(IndexRequest $request)
    {
        $tenant_id = Auth::user()->tenant_id;

        $dataProvider = new DataProvider(SolutionTypeTenant::class);

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return SolutionTypeTenantResource::collection($data);
    }

    /**
     * Store a new Solution Type
     */
    public function store(SolutionTypeCreateRequest $request)
    {
        try {
            $solutionType = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new SolutionTypeTenantResource($solutionType);
    }

    /**
     * Show Solution Type
     */
    public function show(SolutionType $solutionType)
    {
        return new SolutionTypeTenantResource($solutionType);
    }

    /**
     * Update Solution Type
     */
    public function update(SolutionTypeUpdateRequest $request, SolutionType $solutionType)
    {
        try {
            $solutionType = DB::transaction(fn () => $this->service->update($solutionType, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new SolutionTypeTenantResource($solutionType);
    }

    /**
     * Remove Solution Type
     */
    public function destroy(SolutionType $solutionType)
    {
        $solutionType->tenants()->detach(Auth::user()->tenant_id);

        foreach ($solutionType->configurations as $config) {
            $config->delete();
        }

        return response()->json(null, 204);
    }
}
