<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Account;
use App\Helpers\DataProvider;
use App\Services\AccountService;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AccountResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AccountCreateRequest;
use App\Http\Requests\AccountUpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountController extends BaseController
{

    protected $model = Account::class;
    public function __construct(protected AccountService $accountService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index()
    {
        $dataProvider = new DataProvider(Account::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return AccountResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountCreateRequest $request)
    {
        try {
            $account = $this->accountService->store($request->all());
        } catch (Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }
        return new AccountResource($account);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        $temp = new AccountResource($account);
        return $temp->toArray(request());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        try {
            $account = $this->accountService->update($account, $request->all());
        } catch (Exception $e) {
            if (!env(key: 'APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new Exception($e);
        }
        return new AccountResource($account);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json(null, 204);
    }
}
