<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\ContactService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\IndexRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ContactResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ContactCreateRequest;

class ContactController extends BaseController
{
    protected $model = Contact::class;
    public function __construct(protected ContactService $contactService)
    {
        parent::__construct();
    }
    /**
     * Index
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
     * Store
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
     * Show
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /**
     * Update
     */
    public function update(Request $request, Contact $contact)
    {
        try {
            $contact =  DB::transaction(function () use ($contact, $request) {
                return $this->contactService->update($contact, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }
        return new ContactResource($contact);
    }

    /**
     * Remove
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(null, 204);
    }
}
