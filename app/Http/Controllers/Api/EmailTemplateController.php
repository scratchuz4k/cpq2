<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Models\EmailTemplate;
use App\Http\Requests\IndexRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\EmailTemplateResource;

class EmailTemplateController extends BaseController
{
    protected $model = EmailTemplate::class;
    /**
     * Index
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(EmailTemplate::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return EmailTemplateResource::collection($data);
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
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }
}
