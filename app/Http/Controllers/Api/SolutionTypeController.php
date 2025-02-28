<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\SolutionType;
use App\Models\SolutionTypeTenant;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\SolutionTypeResource;

class SolutionTypeController extends BaseController
{
    protected $model = SolutionType::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenant_id = Auth::user()->tenant_id;

        $dataProvider = new DataProvider(SolutionTypeTenant::class);
        $dataProvider->builder = $dataProvider->builder
            ->where('tenant_id', $tenant_id);

        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return SolutionTypeTenantResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SolutionType $solutionType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SolutionType $solutionType)
    {
        //
    }

    /**
         * Remove the specified resource from storage.
         */
    public function destroy(SolutionType $solutionType)
    {
        $solutionType->tenants()->detach(Auth::user()->tenant_id);
        return response()->json(null, 204);
    }
}
