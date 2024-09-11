<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $insertPermissions = collect();

        $simplePermissions = [
            'Admin' => [
                'access admin' => 'Administration Access'
            ],
        ];

        $permissions = [
            'viewAny' => 'List',
            'view' => 'Show',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'restore' => 'Restore',
            'forceDelete' => 'Remove',
        ];

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsByGroup = [
            'Website' => [
                'order' => 'Order',
            ],

        ];

        $roles = [
            'Super Admin' => [],
            'Admin' => ['Admin', 'Website'],
        ];

        foreach ($simplePermissions as $key => $pe) {
            $ids = collect($pe)
                ->map(function ($name, $index) use ($key) {
                    return Permission::updateOrCreate(['name' => "$index", 'guard_name' => 'web'], ['group' => $key, 'title' => "$name"]);
                })
                ->pluck('id')
                ->toArray();

            $insertPermissions[$key] = $ids;
        }

        foreach ($permissionsByGroup as $group => $values) {
            $ids = collect();

            foreach ($permissions as $permissionKey => $permission) {
                $ids->push(...collect($values)
                    ->map(function ($name, $index) use ($group, $permissionKey, $permission) {
                        return Permission::updateOrCreate(['name' => "$permissionKey $index", 'guard_name' => 'web'], ['group' => $group, 'title' => "$permission $name"]);
                    })
                    ->pluck('id')
                    ->toArray());
            }

            $insertPermissions[$group] = $ids;
        }

        foreach ($roles as $role => $groups) {
            $permissions = [];
            $role = Role::updateOrCreate(['name' => $role, 'guard_name' => 'web']);
            foreach ($groups as $group) {
                if (array_key_exists($group, $insertPermissions->toArray())) {
                    $permissions = array_merge($permissions, $insertPermissions->toArray()[$group]);
                }
            }
            $role->syncPermissions($permissions);
        }

        $sAdmin = User::where('name', 'Company Admin User')->first();
        $sAdmin->assignRole('Super Admin');
        $sAdmin->assignRole('Admin');
    }
}
