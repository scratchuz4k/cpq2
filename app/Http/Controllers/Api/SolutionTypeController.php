<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\SolutionType;
use App\Models\SolutionTypeTenant;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SolutionTypeController extends BaseController
{
    protected $model = SolutionType::class;

    /**
     * List of Solution Types
     */
    public function index(IndexRequest $request)
    {
        $tenant_id = Auth::user()->tenant_id;

        $dataProvider = new DataProvider(SolutionTypeTenant::class);

        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return SolutionTypeTenantResource::collection($data);
    }

    /**
     * Store a new Solution Type
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Solution Type
     */
    #[ExcludeRouteFromDocs]
    public function show(SolutionType $solutionType)
    {
        //
    }

    /**
     * Update Solution Type
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, SolutionType $solutionType)
    {
        //
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
