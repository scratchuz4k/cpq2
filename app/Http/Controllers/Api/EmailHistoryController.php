<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\EmailHistoryResource;
use App\Models\EmailHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailHistoryController extends BaseController
{
    protected $model = EmailHistory::class;

    /**
     * Index
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(EmailHistory::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return EmailHistoryResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailHistory $emailHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailHistory $emailHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailHistory $emailHistory)
    {
        //
    }
}
