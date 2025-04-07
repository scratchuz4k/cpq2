<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\UsersService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\UserUpdateRequest;

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
    public function index()
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Show User
     */
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
            $user =  DB::transaction(function () use ($request, $user) {
                return  $this->usersService->update($user, $request->all());
            });
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
