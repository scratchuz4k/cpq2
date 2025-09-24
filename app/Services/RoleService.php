<?php

namespace App\Services;

use App\Models\Role;

class RoleService
{
    public function store(array $array): Role
    {
        $role = Role::create($array);

        return $role;
    }

    public function update(Role $role, array $array): Role
    {
        $role->update($array);

        return $role;
    }
}
