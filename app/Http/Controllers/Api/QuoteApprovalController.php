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
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
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
     * List of Quote Approvals
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
     * Store a new Quote Approval
     */
    #[ExcludeRouteFromDocs]
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

        // TODO: Change this logic to send notification in quoteApprovalService
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
     * Show Quote Approval
     */
    #[ExcludeRouteFromDocs]
    public function show(QuoteApproval $quoteApproval)
    {
        return response()->json(new QuoteApprovalResource($quoteApproval));
    }

    /**
     * Update Quote Approval
     */
    #[ExcludeRouteFromDocs]
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
     * Update Quote Approval by Client
     */
    #[ExcludeRouteFromDocs]
    public function clientApprove(QuoteApprovalUpdateRequest $request, string $uuid)
    {

        $quote = Quote::where('uuid', $uuid)->firstOrFail();
        $quote->tenant->makeCurrent();

        $lastApproval = $quote->approvals->count() ? $quote->approvals[0] : null;
        if (! $lastApproval) {
            return response()->json(['message' => 'This Quote Approval has no current approval workflow'], 422);
        }

        $error = $this->service->validateUpdate($lastApproval);
        if ($error != null) {
            return $error;
        }

        try {
            $lastApproval = DB::transaction(fn () => $this->service->update($lastApproval, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        $quote->tenant->forgetCurrent();

        return response()->json(new QuoteApprovalResource($lastApproval));
    }

    /**
     * Remove Quote Approval
     */
    #[ExcludeRouteFromDocs]
    public function destroy(QuoteApproval $quoteApproval)
    {
        //
    }
}
