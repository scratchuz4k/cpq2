<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\ContactService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends BaseController
{
    protected $model = Contact::class;

    public function __construct(protected ContactService $contactService)
    {
        parent::__construct();
    }

    /**
     * List Contacts
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<ContactResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Contact::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return ContactResource::collection($data);
    }

    /**
     * Store a new Contact
     */
    public function store(ContactCreateRequest $request)
    {
        try {
            $contact = DB::transaction(function () use ($request) {
                return $this->contactService->store($request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new ContactResource($contact);
    }

    /**
     * Show Contact
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /**
     * Update Contact
     */
    public function update(Request $request, Contact $contact)
    {
        try {
            $contact = DB::transaction(function () use ($contact, $request) {
                return $this->contactService->update($contact, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new ContactResource($contact);
    }

    /**
     * Remove Contact
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
