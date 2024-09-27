<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\AccountService;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AccountResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AccountCreateRequest;
use App\Http\Requests\AccountUpdateRequest;

class AccountController extends BaseController
{
    protected $model = Account::class;
    public function __construct(protected AccountService $accountService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Accounts/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Accounts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountCreateRequest $request)
    {
        try {
            $account = $this->accountService->store($request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('accounts.edit', ['account' => $account->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        $temp = new AccountResource($account);
        return Inertia::render('Accounts/Edit', [
            "account" => $temp->toArray(request())
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        try {
            $account = $this->accountService->update($account, $request->all());
        } catch (\Exception $e) {
            if (!env(key: 'APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('accounts.edit', ['account' => $account->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }
}
