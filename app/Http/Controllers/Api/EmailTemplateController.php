<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailTemplateController extends BaseController
{
    protected $model = EmailTemplate::class;

    public function __construct(protected EmailService $emailService)
    {
        parent::__construct();
    }

    /**
     * Index
     *
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
        try {
            $emailTemplate = DB::transaction(fn () => $this->emailService->update($emailTemplate, $request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new EmailTemplateResource($emailTemplate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }
}
