<?php

namespace App\Services;

use App\Imports\UsersImport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UsersService
{
    public function store(array $array): User
    {
        return DB::transaction(function () use ($array) {
            if (array_key_exists('password', $array) && !empty($array['password'])) {
                $array['password'] = Hash::make($array['password']);
            }

            $user = User::create($array);

            if (array_key_exists('role_id', $array) && !empty($array['role_id'])) {
                $user->assignRole($array['role_id']);
            } else {
                $user->assignRole(User::$default_role);
            }

            if (array_key_exists('tenants', $array)) {
                $user->tenants()->sync($array['tenants']);
            }

            return $user;
        });
    }

    public function update(User $user, array $array): User
    {
        if (array_key_exists('password', $array) && !empty($array['password'])) {
            $array['password'] = Hash::make($array['password']);
        } else {
            $array['password'] = $user->password;
        }

        return DB::transaction(function () use ($user, $array) {

            $user->update($array);

            if (array_key_exists('role_id', $array)) {
                $user->syncRoles($array['role_id']);
            }

            if (array_key_exists('tenants', $array)) {
                $user->tenants()->sync($array['tenants']);
            }

            return $user;
        });
    }

    public function import(array $array)
    {
        return DB::transaction(function () use ($array) {
            $import = new UsersImport();
            Excel::import($import, $array['file']);

            return $import->results;
        });
    }

    public function getChildAndOrphansUser(): Builder
    {
        $query = User::query();
        $superUsers = collect([]);
        if (!request()->user()->hasRole('Super Admin')) {
            $superUsers = User::whereHas('roles', function ($query) {
                return $query->whereIn('name', ['Super Admin']);
            })->get();
        }
        $query = $query->whereNotIn('id', $superUsers->pluck('id'));

        $roles = Role::descendants();
        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('id', $roles->pluck('id'));
        });
    }
}
