<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    protected $model = Activity::class;

    /**
     * List of Activities
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ActivityResource>>
     */
    #[ExcludeRouteFromDocs]
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
     * Create Activity
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Activity
     */
    #[ExcludeRouteFromDocs]
    public function show(Activity $Activity)
    {
        //
    }

    /**
     * Update Activity
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Activity $Activity)
    {
        //
    }

    /**
     * Remove Activity
     */
    #[ExcludeRouteFromDocs]
    public function destroy(Activity $Activity)
    {
        //
    }
}
