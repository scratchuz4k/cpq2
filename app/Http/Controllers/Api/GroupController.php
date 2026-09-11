<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\GroupResource;
use App\Models\Account;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends BaseController
{
    public function __construct(protected GroupService $groupService) {}

    /**
     * List of User Groups
     */
    public function index(IndexRequest $request)
    {

        $dataProvider = new DataProvider(Group::class);

        $viewer = $request->user();
        if ($viewer && $viewer->isGrouped()) {
            $allowed = $viewer->groupIds();

            // CPQ-527: when editing another user / an account, also expose that
            // record's own groups so they can be shown (locked) in the picker.
            $forUserId = (int) $request->input('for_user_id');
            if ($forUserId) {
                $target = User::find($forUserId);
                if ($target && $viewer->can('update', $target)) {
                    $allowed = $allowed->merge($target->groupIds());
                }
            }

            $forAccountId = (int) $request->input('for_account_id');
            if ($forAccountId) {
                $account = Account::find($forAccountId);
                if ($account && $viewer->can('update', $account)) {
                    $allowed = $allowed->merge($account->groups()->pluck('groups.id'));
                }
            }

            $dataProvider->builder = $dataProvider->builder->whereIn('id', $allowed->unique()->values()->all());
        }

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return GroupResource::collection($data);
    }

    /**
     * Store a new User Group
     */
    public function store(Request $request)
    {
        try {
            $group = DB::transaction(fn () => $this->groupService->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupResource($group));
    }

    /**
     * Show User Group
     */
    public function show(Group $group)
    {
        return new GroupResource($group);
    }

    /**
     * Update User Group
     */
    public function update(Request $request, Group $group)
    {
        try {
            $group = DB::transaction(fn () => $this->groupService->update($group, $request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupResource($group));
    }

    /**
     * Remove User Group
     */
    public function destroy(Group $group)
    {
        $group->delete();

        return response(null, 200);
    }
}
