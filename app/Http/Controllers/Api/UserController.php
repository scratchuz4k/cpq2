<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UsersService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    protected $model = User::class;

    public function __construct(protected UsersService $usersService)
    {
        parent::__construct();
    }

    /**
     * List of Users
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(User::class);
        $dataProvider->builder = $this->usersService->getChildAndOrphansUser();
        $dataProvider->builder = $dataProvider->builder->where('tenant_id', request()->user()->tenant_id);
        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());

            return $e->getMessage();
        }

        return UserResource::collection($data);
    }

    /**
     * Store a new User
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show User
     */
    #[ExcludeRouteFromDocs]
    public function show(User $user)
    {
        //
    }

    /**
     * Update User
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $user = DB::transaction(fn () => $this->usersService->update($user, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return new UserResource($user);
    }

    /**
     * Remove User
     */
    public function destroy(User $user)
    {
        $user->delete();
    }
}
