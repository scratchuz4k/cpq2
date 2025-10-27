<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteApprovalCreateRequest;
use App\Http\Requests\QuoteApprovalUpdateRequest;
use App\Http\Resources\QuoteApprovalResource;
use App\Models\QuoteApproval;
use App\Services\QuoteApprovalService;
use Exception;
use Illuminate\Support\Facades\DB;

class QuoteApprovalController extends BaseController
{
    protected $model = QuoteApproval::class;

    public function __construct(protected QuoteApprovalService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $dataProvider = new DataProvider($this->model);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return QuoteApprovalResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteApprovalCreateRequest $request)
    {
        try {
            $quoteApproval = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteApprovalResource($quoteApproval));
    }

    /**
     * Display the specified resource.
     */
    public function show(QuoteApproval $quoteApproval)
    {
        return response()->json(new QuoteApprovalResource($quoteApproval));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteApprovalUpdateRequest $request, QuoteApproval $quoteApproval)
    {
        try {
            $quoteApproval = DB::transaction(fn () => $this->service->update($quoteApproval, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteApprovalResource($quoteApproval));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuoteApproval $quoteApproval)
    {
        //
    }
}
