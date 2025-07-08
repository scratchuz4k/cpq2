<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Services\RegionsService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegionController extends BaseController
{
    protected $model = Region::class;

    public function __construct(protected RegionsService $service)
    {
        parent::__construct();
    }

    /**
     * List of Regions
     * @response AnonymousResourceCollection<LengthAwarePaginator<RegionResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Region::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
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
        try {
            $region = DB::transaction(function () use ($region, $request) {
                return $this->service->update($region, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new RegionResource($region);
    }

    /**
     * Remove Region
     */
    public function destroy(Region $region)
    {
        $region->delete();
    }
}
