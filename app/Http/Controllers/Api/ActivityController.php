<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Exception;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    protected $model = Activity::class;

    /**
     * Index
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ActivityResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Activity::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
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
