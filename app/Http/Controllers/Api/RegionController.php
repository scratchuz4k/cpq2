<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class RegionController extends BaseController
{
    protected $model = Region::class;

    /**
     * List of Regions
     * @response AnonymousResourceCollection<LengthAwarePaginator<RegionResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Region::class);
        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return RegionResource::collection($data);
    }

    /**
     * Store a new Region
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Region
     */
    #[ExcludeRouteFromDocs]
    public function show(Region $region)
    {
        //
    }

    /**
     * Update Region
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Region $region)
    {
        //
    }

    /**
     * Remove Region
     */
    public function destroy(Region $region)
    {
        $region->delete();
    }
}
