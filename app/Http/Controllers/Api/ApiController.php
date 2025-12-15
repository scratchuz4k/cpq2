<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ApiResource;
use App\Models\Api;
use App\Services\ApiService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Api::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return ApiResource::collection($data);
    }

    /**
     * Create API
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show API
     */
    #[ExcludeRouteFromDocs]
    public function show(Api $api)
    {
        //
    }

    /**
     * Update API
     */
    public function update(Request $request, Api $api)
    {
        try {
            $api = DB::transaction(fn () => $this->apiService->update($api, $request->all()));
        } catch (\Exception $e) {
            return handleException($e);
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
