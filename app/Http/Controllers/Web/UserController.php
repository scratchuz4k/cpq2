<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserImportRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Support\Facades\DB;
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
        $users = $this->usersService->getChildAndOrphansUser();
        $users = $users->where('tenant_id', request()->user()->tenant_id);
        $noManagerOptions = collect([
            [
                'id' => 0,
                'name' => 'No manager',
                'parent_id' => null,
            ],
        ]);

        return Inertia::render('Users/Create', [
            'managers' => $noManagerOptions->merge($users->get()),
            'parent_id' => $users->where('id', request('parent_id'))->first() ? intval(request('parent_id')) : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {
        try {
            $user = DB::transaction(fn () => $this->usersService->store($request->all()));
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
        $users = $this->usersService->getChildAndOrphansUser();
        $users = $users->where('tenant_id', request()->user()->tenant_id);
        $users = $users->where('id', '!=', $user->id);
        $noManagerOptions = collect([
            [
                'id' => 0,
                'name' => 'No manager',
                'parent_id' => null,
            ],
        ]);

        return Inertia::render('Users/Edit', [
            'user' => (new UserResource($user->load(['tenants'])))->toArray(request()),
            'managers' => $noManagerOptions->merge($users->get()),
            'permissions' => $user->getAllPermissions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $user = DB::transaction(fn () => $this->usersService->update($user, $request->all()));
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
            $results = DB::transaction(fn () => $this->usersService->import($request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return Inertia::render('Users/Import', [
            'results' => $results,
        ]);
    }
}
