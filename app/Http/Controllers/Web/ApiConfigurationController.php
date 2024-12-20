<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\DB;
use App\Enums\CloudMarketSuppliersEnum;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiConfigurationResource;
use App\Services\ApiConfigurationService;

class ApiConfigurationController extends BaseController
{
    protected $model = ApiConfiguration::class;
    public function __construct(protected ApiConfigurationService $apiConfiguratioService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Api/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Api/Create', [
            'suppliers' => CloudMarketSuppliersEnum::asArray()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $api = DB::transaction(function () use ($request) {
                return $this->apiConfiguratioService->store($request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('apis.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiConfiguration $apiConfiguration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiConfiguration $api)
    {
        return Inertia::render('Api/Edit', [
            'api' => (new ApiConfigurationResource($api))->resolve(),
            'suppliers' => CloudMarketSuppliersEnum::asArray()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiConfiguration $api)
    {
        try {
            $api = DB::transaction(function () use ($api, $request) {
                return $this->apiConfiguratioService->update($api, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('apis.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiConfiguration $api)
    {
        $api->delete();
        return redirect()->route('apis.index');
    }
}
