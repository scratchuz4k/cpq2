<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupAccountCreateRequest;
use App\Http\Resources\GroupAccountResource;
use App\Http\Resources\GroupResource;
use App\Models\GroupAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupAccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $dataProvider = new DataProvider(GroupAccount::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return GroupAccountResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupAccountCreateRequest $request)
    {
        try {
            $groupAccount = DB::transaction(function () use ($request) {
                return GroupAccount::create($request->all());
            });
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response()->json(new GroupResource($groupAccount));
    }

    /**
     * Display the specified resource.
     */
    public function show(GroupAccount $groupAccount)
    {
        return new GroupResource($groupAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroupAccount $groupAccount)
    {
        try {
            $groupAccount = DB::transaction(function () use ($request, $groupAccount) {
                return $groupAccount->update($request->all());
            });
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }

            return response(['message' => $e->getMessage()], 500);
        }

        return response()->json(new GroupAccountResource($groupAccount));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroupAccount $groupAccount)
    {
        $groupAccount->delete();

        return response(null, 200);
    }
}
