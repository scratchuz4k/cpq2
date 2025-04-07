<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class RegionController extends BaseController
{
    protected $model = Region::class;

    /**List of Regions
     * @response AnonymousResourceCollection<LengthAwarePaginator<RegionResource>>
     */
    public function index()
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Region
     */
    public function show(Region $region)
    {
        //
    }

    /**
     * Update Region
     */
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
