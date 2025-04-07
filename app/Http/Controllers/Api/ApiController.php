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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
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
        return new ApiResource($api);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Api $api)
    {
        $api->delete();
    }
}
