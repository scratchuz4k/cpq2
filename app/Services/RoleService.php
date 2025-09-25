<?php

namespace App\Services;

use App\Models\Role;

class RoleService
{
    public function store(array $array): Role
    {
        $role = Role::create($array);

        if (array_key_exists('permissions', $array)) {
            $role->syncPermissions($array['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $array): Role
    {
        $role->update($array);

        if (array_key_exists('permissions', $array)) {
            $role->syncPermissions($array['permissions']);
        }

        return $role;
    }
}
