<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends BaseController
{
    protected $model = Group::class;

    public function __construct(protected GroupService $groupService)
    {
        parent::__construct();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Group $group): Response
    {
        return Inertia::render('Groups/Edit', [
            'group' => (new GroupResource($group))->toArray(request()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        try {
            $category = DB::transaction(fn () => $this->groupService->update($group, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('users.index');

    }
}
