<?php

namespace App\Http\Controllers\Web;

use App\Enums\ImportsEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AccountCreateRequest;
use App\Http\Requests\AccountUpdateRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
        $user = auth()->user();

        return Inertia::render('Accounts/Index', [
            'imports' => $user->can('import account')
                ? ImportsEnum::asArrayFor([ImportsEnum::Account, ImportsEnum::Contact])
                : [],
        ]);
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
            $account = DB::transaction(fn () => $this->accountService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        $returnTo = $request->query('return_to');
        if ($returnTo && Route::has($returnTo)) {
            return redirect()->route($returnTo, ['account_reference' => $account->reference]);
        }

        return redirect()->route('accounts.index');
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
        return Inertia::render('Accounts/Edit', [
            'account' => (new AccountResource($account))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        try {
            $account = DB::transaction(fn () => $this->accountService->update($account, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('accounts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }
}
