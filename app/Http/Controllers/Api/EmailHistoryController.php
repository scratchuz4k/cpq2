<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\EmailHistoryResource;
use App\Models\EmailHistory;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;

class EmailHistoryController extends BaseController
{
    protected $model = EmailHistory::class;

    /**
     * List of Email History
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(EmailHistory::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return EmailHistoryResource::collection($data);
    }

    /**
     * Store a new Email History
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Email History
     */
    #[ExcludeRouteFromDocs]
    public function show(EmailHistory $emailHistory)
    {
        //
    }

    /**
     * Update Email History
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, EmailHistory $emailHistory)
    {
        //
    }

    /**
     * Remove Email History
     */
    #[ExcludeRouteFromDocs]
    public function destroy(EmailHistory $emailHistory)
    {
        //
    }
}
