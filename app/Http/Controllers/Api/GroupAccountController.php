<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupAccountCreateRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\GroupAccountResource;
use App\Http\Resources\GroupResource;
use App\Models\GroupAccount;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupAccountController extends BaseController
{
    /**
     * List of Group Accounts
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(GroupAccount::class);

        // A soft-deleted account leaves its pivot row behind; skip those so the
        // group never shows a membership row with no account name.
        $dataProvider->builder = $dataProvider->builder->whereHas('account');

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return GroupAccountResource::collection($data);
    }

    /**
     * Store a new Group Account
     */
    #[ExcludeRouteFromDocs]
    public function store(GroupAccountCreateRequest $request)
    {
        try {
            $groupAccount = DB::transaction(fn () => GroupAccount::create($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupResource($groupAccount));
    }

    /**
     * Show Group Account
     */
    #[ExcludeRouteFromDocs]
    public function show(GroupAccount $groupAccount)
    {
        return new GroupResource($groupAccount);
    }

    /**
     * Update Group Account
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, GroupAccount $groupAccount)
    {
        try {
            $groupAccount = DB::transaction(fn () => $groupAccount->update($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupAccountResource($groupAccount));
    }

    /**
     * Remove Group Account
     */
    #[ExcludeRouteFromDocs]
    public function destroy(GroupAccount $groupAccount)
    {
        $user = auth()->user();

        // CPQ-530: grouped users may only remove an account from a group they belong to.
        if ($user && $user->isGrouped() && ! $user->groupIds()->contains($groupAccount->group_id)) {
            abort(403);
        }

        $groupAccount->delete();

        return response(null, 200);
    }
}
