<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AccountCreateRequest;
use App\Http\Requests\AccountUpdateRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountController extends BaseController
{
    protected $model = Account::class;

    public function __construct(protected AccountService $accountService)
    {
        parent::__construct();
    }

    /**
     * List Accounts
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Account::class);

        $user = request()->user();
        if ($user->group()) {
            $ids = $user->group()->accounts->pluck('id');
            $dataProvider->builder = $dataProvider->builder->whereIn('id', $ids);
        }

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return AccountResource::collection($data);
    }

    /**
     * Store a new Account
     */
    public function store(AccountCreateRequest $request)
    {
        try {
            $account = DB::transaction(fn () => $this->accountService->store($request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new AccountResource($account);
    }

    /**
     * Show Account
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * Update Account
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        try {
            $account = DB::transaction(fn () => $this->accountService->update($account, $request->all()));
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
        }

        return new AccountResource($account);
    }

    /**
     * Remove Account
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return response()->json(null, 204);
    }
}
