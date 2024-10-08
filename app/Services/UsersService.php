<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersService
{
    public function store(array $array): User
    {
        return DB::transaction(function () use ($array) {
            if (array_key_exists('password', $array) && !empty($array['password'])) {
                $array['password'] = Hash::make($array['password']);
            }

            $user = User::create([
                ...$array,
                "tenant_id" => app("currentTenant")->id,
            ]);

            if (array_key_exists('role_id', $array)) {
                $user->assignRole($array['role_id']);
            }

            return $user;
        });
    }

    public function update(User $user, array $array): User
    {
        $array['password'] = Hash::make($array['password']);
        return DB::transaction(function () use ($user, $array) {

            if (array_key_exists('password', $array) && !empty($array['password'])) {
                $array['password'] = Hash::make($array['password']);
            }

            $user->update(
                [
                    ...$array,
                    'tenant_id' => app('currentTenant')->id,
                ]
            );

            if (array_key_exists('role_id', $array)) {
                $user->syncRoles($array['role_id']);
            }

            return $user;
        });
    }
}
