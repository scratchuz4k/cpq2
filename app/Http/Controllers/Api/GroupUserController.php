<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupUserCreateRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Models\GroupUser;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupUserController extends BaseController
{
    /**
     * List of Group Users
     */
    #[ExcludeRouteFromDocs]
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(GroupUser::class);

        // Only surface memberships whose user still exists so a removed user
        // never lingers in the group as a nameless row. `user.groups` is eager
        // loaded because the group page needs the current group list to remove a
        // member.
        $dataProvider->builder = $dataProvider->builder
            ->with(['user.groups', 'group'])
            ->whereHas('user');

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return GroupUserResource::collection($data);
    }

    /**
     * Store a new Group User
     */
    #[ExcludeRouteFromDocs]
    public function store(GroupUserCreateRequest $request)
    {
        try {
            $groupUser = DB::transaction(fn () => GroupUser::create($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupResource($groupUser));
    }

    /**
     * Show Group User
     */
    #[ExcludeRouteFromDocs]
    public function show(GroupUser $groupUser)
    {
        return new GroupResource($groupUser);
    }

    /**
     * Update Group User
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, GroupUser $groupUser)
    {
        try {
            $groupUser = DB::transaction(fn () => $groupUser->update($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new GroupUserResource($groupUser));
    }

    /**
     * Remove Group User
     */
    #[ExcludeRouteFromDocs]
    public function destroy(GroupUser $groupUser)
    {
        $user = auth()->user();

        // CPQ-530: grouped users may only remove a user from a group they belong to.
        if ($user && $user->isGrouped() && ! $user->groupIds()->contains($groupUser->group_id)) {
            abort(403);
        }

        $groupUser->delete();

        return response(null, 200);
    }
}
