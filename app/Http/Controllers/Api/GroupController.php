<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends BaseController
{
    public function __construct(protected GroupService $groupService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $dataProvider = new DataProvider(Group::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return GroupResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $group = DB::transaction(fn() => $this->groupService->store($request->all()));
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        return response()->json(new GroupResource($group));
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        return new GroupResource($group);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        try {
            $group = DB::transaction(fn() => $this->groupService->update($group, $request->all()));
        } catch (Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }

            return response(['message' => $e->getMessage()], 500);
        }

        return response()->json(new GroupResource($group));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();

        return response(null, 200);
    }
}
