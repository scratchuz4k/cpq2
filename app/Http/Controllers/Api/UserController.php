<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $model = User::class;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(User::class);

        $userDescedents = collect([auth()->user()]);
        $ids = collect([]);
        do {
            $current = $userDescedents->pop();
            $ids->push($current->id);
            $userDescedents->push(...$current->children);
        } while (count($userDescedents));

        $dataProvider->builder = $dataProvider->builder->whereIn('id', $ids);
        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return UserResource::collection($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
    }
}
