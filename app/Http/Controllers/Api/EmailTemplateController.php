<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\EmailTemplateResource;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailTemplateController extends BaseController
{
    protected $model = EmailTemplate::class;

    public function __construct(protected EmailService $emailService)
    {
        parent::__construct();
    }

    /**
     * List Email Templates
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(EmailTemplate::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return EmailTemplateResource::collection($data);
    }

    /**
     * Store a new Email Template
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Email Template
     */
    #[ExcludeRouteFromDocs]
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Update Email Template
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        try {
            $emailTemplate = DB::transaction(fn () => $this->emailService->update($emailTemplate, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return new EmailTemplateResource($emailTemplate);
    }

    /**
     * Remove Email Template
     */
    #[ExcludeRouteFromDocs]
    public function destroy(EmailTemplate $emailTemplate)
    {
        //
    }
}
