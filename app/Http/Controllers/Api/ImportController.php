<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ImportResource;
use App\Models\Import;
use App\Services\ImportService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends BaseController
{
    protected $model = Import::class;

    public function __construct(protected ImportService $importService)
    {
        parent::__construct();
    }

    /**
     * List Imports
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ImportResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Import::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return ImportResource::collection($data);
    }

    /**
     * Create a new Import
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        try {
            $data = $this->importService->preview($request->all());
        } catch (\Exception $e) {
            return handleException($e);
        }

        return response()->json($data);
    }

    /**
     * Show Import
     */
    #[ExcludeRouteFromDocs]
    public function show(Import $import)
    {
        //
    }

    /**
     * Update Import
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Import $import)
    {
        //
    }

    /**
     * Remove Import
     */
    public function destroy(Import $import)
    {
        $import->delete();
    }
}
