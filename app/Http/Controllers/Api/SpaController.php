<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\SpaCreateRequest;
use App\Http\Requests\SpaUpdateRequest;
use App\Http\Resources\SpaResource;
use App\Models\Spa;
use App\Services\SpasService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpaController extends BaseController
{
    protected $model = Spa::class;

    public function __construct(protected SpasService $spasService)
    {
        parent::__construct();
    }

    /**
     * List of Spas
     *
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
    public function store(SpaCreateRequest $request)
    {
        try {
            $spa = DB::transaction(fn () => $this->spasService->store($request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new SpaResource($spa->refresh());
    }

    /**
     * Show Spa
     */
    #[ExcludeRouteFromDocs]
    public function show(Spa $spa)
    {
        return new SpaResource($spa);
    }

    /**
     * Update Spa
     */
    #[ExcludeRouteFromDocs]
    public function update(SpaUpdateRequest $request, Spa $spa)
    {
        try {
            $spa = DB::transaction(fn () => $this->spasService->update($spa, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new SpaResource($spa->refresh());
    }

    /**
     * Remove Spa
     */
    public function destroy(Spa $spa)
    {
        $spa->delete();
    }
}
