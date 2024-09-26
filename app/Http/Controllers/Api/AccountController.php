<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AccountResource;

class AccountController extends BaseController
{
    protected $model = Account::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(Account::class);
        try {
            $filters = json_decode(request()->input('filters', '[]'), true);
            $data = $dataProvider->get($filters);
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return AccountResource::collection($data);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete();
    }
}
