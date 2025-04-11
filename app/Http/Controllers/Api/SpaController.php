<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\SpaCreateRequest;
use App\Http\Requests\SpaUpdateRequest;
use App\Http\Resources\SpaResource;
use App\Models\Spa;
use App\Services\SpasService;
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
     * Display a listing of the resource.
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<SpaResource>>
     */
    public function index()
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
     * Store a newly created resource in storage.
     */
    public function store(SpaCreateRequest $request)
    {
        try {
            $spa = DB::transaction(function () use ($request) {
                return $this->spasService->store($request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new SpaResource($spa->refresh());
    }

    /**
     * Display the specified resource.
     */
    public function show(Spa $spa)
    {
        return new SpaResource($spa);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpaUpdateRequest $request, Spa $spa)
    {
        try {
            $spa = DB::transaction(function () use ($spa, $request) {
                return $this->spasService->update($spa, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new SpaResource($spa->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spa $spa)
    {
        $spa->delete();
    }
}
