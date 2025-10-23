<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\QuoteApprovalResource;
use App\Models\QuoteApproval;
use App\Services\QuoteApprovalService;
use Exception;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {

        try {
            $group = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteApprovalResource($group));
    }

    /**
     * Display the specified resource.
     */
    public function show(QuoteApproval $quoteApproval)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QuoteApproval $quoteApproval)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuoteApproval $quoteApproval)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuoteApproval $quoteApproval)
    {
        //
    }
}
