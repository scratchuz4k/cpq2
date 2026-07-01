<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $dataProvider->builder = User::getChildAndOrphansUser();

        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            return handleException($e);
        }

        return UserResource::collection($data);
    }

    /**
     * Store a new User
     */
    public function store(Request $request)
    {
        try {
            $user = DB::transaction(fn () => $this->usersService->store($request->all()));
        } catch (\Exception $e) {
            return handleException($e);
        }

        return new UserResource($user);
    }

    /**
     * Show User
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update User
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $user = DB::transaction(fn () => $this->usersService->update($user, $request->all()));
        } catch (\Exception $e) {
            return handleException($e);
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
