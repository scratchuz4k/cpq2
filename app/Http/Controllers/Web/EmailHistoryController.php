<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\EmailHistoryResource;
use App\Models\EmailHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        //
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
        $temp = (new EmailHistoryResource($emailHistory))->resolve();

        return Inertia::render('Emails/Show', ['email' => $temp]);
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
