<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ActivityResource;
use Illuminate\Support\Facades\File;

class ActivityController extends BaseController
{
    protected $model = Activity::class;
    /**
     * Index
     * @response AnonymousResourceCollection<LengthAwarePaginator<ActivityResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Activity::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return ActivityResource::collection($data);
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show.
     */
    public function show(Activity $Activity)
    {
        //
    }

    /**
     * Update
     */
    public function update(Request $request, Activity $Activity)
    {
        //
    }

    /**
     * Remove
     */
    public function destroy(Activity $Activity)
    {
        //
    }
}
