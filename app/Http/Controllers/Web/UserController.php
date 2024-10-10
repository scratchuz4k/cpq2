<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UserImportRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Http\Request;
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
        return Inertia::render('Users/Create', [
            'roles'     => Role::all(),
            'managers'  => User::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        $temp = new UserResource($user);
        $temp['role'] = $user->roles->first()->id;
        return Inertia::render('Users/Edit', [
            'user'      => $temp->toArray(request()),
            'roles'     => Role::all(),
            'managers'  => User::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
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
