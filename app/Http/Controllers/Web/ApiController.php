<?php

namespace App\Http\Controllers\Web;

use App\Enums\CloudMarketSuppliersEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ApiCreateRequest;
use App\Http\Resources\ApiResource;
use App\Models\Api;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ApiController extends BaseController
{
    protected $model = Api::class;

    public function __construct(protected ApiService $apiService)
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
            'suppliers' => CloudMarketSuppliersEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiCreateRequest $request)
    {
        try {
            $api = DB::transaction(function () use ($request) {
                return $this->apiService->store($request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('apis.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Api $api)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Api $api)
    {
        return Inertia::render('Api/Edit', [
            'api' => (new ApiResource($api))->resolve(),
            'suppliers' => CloudMarketSuppliersEnum::asArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Api $api)
    {
        try {
            $api = DB::transaction(function () use ($api, $request) {
                return $this->apiService->update($api, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('apis.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Api $api)
    {
        $api->delete();

        return redirect()->route('apis.index');
    }
}
