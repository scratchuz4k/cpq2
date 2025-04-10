<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\SpaResource;
use App\Models\Spa;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpaController extends BaseController
{
    protected $model = Spa::class;
    /**
     * List of Spas
     * @response AnonymousResourceCollection<LengthAwarePaginator<SpaResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Spa::class);
        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return SpaResource::collection($data);
    }

    /**
     * Store a new Spa
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Spa
     */
    #[ExcludeRouteFromDocs]
    public function show(Spa $spa)
    {
        //
    }

    /**
     * Update Spa
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Spa $spa)
    {
        //
    }

    /**
     * Remove Spa
     */
    public function destroy(Spa $spa)
    {
        $spa->delete();
    }
}
