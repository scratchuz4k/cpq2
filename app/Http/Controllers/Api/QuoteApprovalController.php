<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationEnum;
use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteApprovalCreateRequest;
use App\Http\Requests\QuoteApprovalUpdateRequest;
use App\Http\Resources\QuoteApprovalResource;
use App\Models\Quote;
use App\Models\QuoteApproval;
use App\Models\Tenant;
use App\Models\User;
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
        $error = $this->service->validateStore(Quote::find($request->quote_id));
        if ($error != null) {
            return $error;
        }

        try {
            $quoteApproval = DB::transaction(fn () => $this->service->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        // Get the current tenant
        $users = User::hasPermission('update quote_approval')->get();

        foreach ($users as $user) {
            // Send notification to each user
            $description = 'A new quote approval request has been submitted for '.$quoteApproval->quote->account->name.' and is awaiting review';
            createNotification(NotificationEnum::QuoteSubmitted, $user, $description, route('quotes.show', ['quote' => $request->quote_id], false));
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
        $error = $this->service->validateUpdate($quoteApproval);
        if ($error != null) {
            return $error;
        }

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
