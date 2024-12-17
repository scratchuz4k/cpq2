<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Account;
use App\Helpers\DataProvider;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\IndexRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AccountResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AccountCreateRequest;
use App\Http\Requests\AccountUpdateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends BaseController
{

    protected $model = Account::class;
    public function __construct(protected AccountService $accountService)
    {
        parent::__construct();
    }
    /**
     * Index
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index(IndexRequest $request)
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
     * Store
     */
    public function store(AccountCreateRequest $request)
    {
        try {
            $account = DB::transaction(function () use ($request) {
                return $this->accountService->store($request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }
        return new AccountResource($account);
    }

    /**
     * Show.
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * Update
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        try {
            $account = DB::transaction(function () use ($account, $request) {
                return $this->accountService->update($account, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }
        return new AccountResource($account);
    }

    /**
     * Remove
     */
    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json(null, 204);
    }
}
