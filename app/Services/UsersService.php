<?php

namespace App\Services;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UsersService
{
    public function store(array $array): User
    {
        if (array_key_exists('password', $array) && ! empty($array['password'])) {
            $array['password'] = Hash::make($array['password']);
        }

        if (array_key_exists('parent_id', $array) && $array['parent_id'] == 0) {
            $array['parent_id'] = null;
        }

        $user = User::create($array);

        if (array_key_exists('group_id', $array)) {
            $temp = [];
            $temp[$array['group_id']] = ['is_default' => true];
            $user->groups()->sync($temp);
        }

        if (array_key_exists('role_id', $array) && ! empty($array['role_id'])) {
            $user->assignRole($array['role_id']);
        } else {
            $user->assignRole(User::$default_role);
        }

        if ($user->tenant_id) {
            $user->tenants()->sync($user->tenant_id);
        }

        return $user;
    }

    public function update(User $user, array $array): User
    {
        if (array_key_exists('password', $array) && ! empty($array['password'])) {
            $array['password'] = Hash::make($array['password']);
        } else {
            $array['password'] = $user->password;
        }

        $user->update($array);

        if (array_key_exists('group_id', $array)) {
            $temp = [];
            $temp[$array['group_id']] = ['is_default' => true];
            $user->groups()->sync($temp);
        }

        if (array_key_exists('role_id', $array)) {
            $user->syncRoles($array['role_id']);
        }

        if ($user->tenant_id) {
            $user->tenants()->sync($user->tenant_id);
        }

        return $user;
    }

    public function import(array $array)
    {
        $import = new UsersImport;
        Excel::import($import, $array['file']);

        return $import->results;
    }
}
