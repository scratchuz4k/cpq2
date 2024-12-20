<?php

namespace App\Http\Controllers\Api;

use Exception;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiConfigurationResource;

class ApiConfigurationController extends BaseController
{
    protected $model = ApiConfiguration::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(ApiConfiguration::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return ApiConfigurationResource::collection($data);
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
    public function show(ApiConfiguration $apiConfiguration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiConfiguration $apiConfiguration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiConfiguration $apiConfiguration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiConfiguration $api)
    {
        $api->delete();
    }
}
