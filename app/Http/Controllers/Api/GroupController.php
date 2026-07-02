<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
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
