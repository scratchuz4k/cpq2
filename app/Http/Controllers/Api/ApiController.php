<?php

namespace App\Http\Controllers\Api;

use App\Services\ApiService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Models\Api;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;

class ApiController extends BaseController
{
    protected $model = Api::class;

    public function __construct(protected ApiService $apiService)
    {
        parent::__construct();
    }

    /**
     * List APIs
     */
    public function index()
    {
        $dataProvider = new DataProvider(Api::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return ApiResource::collection($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Create API
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show API
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
        //
    }

    /**
     * Update API
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
        return new ApiResource($api);
    }

    /**
     * Remove API
     */
    public function destroy(Api $api)
    {
        $api->delete();
    }
}
