<?php

namespace App\Services;

use App\Imports\UsersImport;
use App\Models\User;
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
}
