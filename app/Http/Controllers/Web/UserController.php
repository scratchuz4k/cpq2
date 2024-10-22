<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserImportRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends BaseController
{
    protected $model = User::class;

    public function __construct(protected UsersService $usersService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Users/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Child Roles
        $temp_roles = Auth::user()->roles()->get();
        $roles = collect([]);

        do {
            $current = $temp_roles->pop();
            $roles->push($current);
            $temp_roles->push(...$current->children);
        } while (count($temp_roles));

        //Child Users
        $users = collect([]);
        $temp_users = collect([Auth::user()]);
        do {
            $current = $temp_users->pop();
            $users->push($current);
            $temp_users->push(...$current->children);
        } while (count($temp_users));

        return Inertia::render('Users/Create', [
            'roles'     => $roles,
            'managers'  => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {
        try {
            $user = $this->usersService->store($request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //Child Roles
        $temp_roles = Auth::user()->roles()->get();
        $roles = collect([]);

        do {
            $current = $temp_roles->pop();
            $roles->push($current);
            $temp_roles->push(...$current->children);
        } while (count($temp_roles));

        //Child Users
        $users = collect([]);
        $temp_users = collect([Auth::user()]);
        do {
            $current = $temp_users->pop();
            $users->push($current);
            $temp_users->push(...$current->children);
        } while (count($temp_users));

        $temp = new UserResource($user);
        $permissions = $user->getAllPermissions();
        return Inertia::render('Users/Edit', [
            'user'      => $temp->toArray(request()),
            'roles'     => $roles,
            'managers'  => $users,
            'permissions'  => $permissions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $user = $this->usersService->update($user, $request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Import users from CSV file
     */
    public function importUsers(UserImportRequest $request)
    {
        try {
            $results = $this->usersService->import($request->all());
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return Inertia::render('Users/Import', [
            'results' => $results
        ]);
    }
}
