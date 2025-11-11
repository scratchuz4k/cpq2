<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleTenantSeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::first();
        $tenant->makeCurrent();

        $insertPermissions = collect();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = RolePermissionSeeder::$roles;

        foreach (RolePermissionSeeder::$simplePermissions as $key => $pe) {
            $ids = collect($pe)
                ->map(function ($name, $index) use ($key) {
                    return Permission::updateOrCreate(['name' => "$index", 'guard_name' => 'web'], ['group' => $key, 'title' => "$name"]);
                })
                ->pluck('id')
                ->toArray();

            $insertPermissions[$key] = $ids;
        }

        foreach (RolePermissionSeeder::$permissionsByGroup as $group => $values) {
            foreach ($values as $valueKey => $value) {
                $ids = collect();
                $ids->push(...collect(RolePermissionSeeder::$permissions)
                    ->map(function ($name, $index) use ($group, $valueKey, $value) {
                        return Permission::updateOrCreate(['name' => "$index $valueKey", 'guard_name' => 'web'], ['group' => $group, 'title' => "$name $value"]);
                    })
                    ->pluck('id')
                    ->toArray());
                $insertPermissions[$valueKey] = $ids;
            }
        }

        foreach (Tenant::all() as $tenant) {
            foreach ($roles as $role => $item) {
                $permissions = [];
                $temp = null;

                if (! is_null($item['parent'])) {
                    $temp = Role::withoutGlobalScopes()->where('name', $item['parent'])->firstOrFail();
                }

                $role = Role::updateOrCreate(['name' => $role, 'tenant_id' => $tenant->id], ['guard_name' => 'web', 'parent_id' => $temp?->id]);
                foreach ($item['permissions'] as $group) {
                    if (array_key_exists($group, $insertPermissions->toArray())) {
                        $permissions = array_merge($permissions, $insertPermissions->toArray()[$group]);
                    }
                }

                if (! empty($item['deny_permission'] ?? [])) {
                    $denyPatterns = array_map(
                        fn ($action) => $action.' %',
                        $item['deny_permission']
                    );

                    $permissionsToRemove = Permission::whereIn('id', $permissions)
                        ->where(function ($query) use ($denyPatterns) {
                            foreach ($denyPatterns as $pattern) {
                                $query->orWhere('name', 'like', $pattern);
                            }
                        })
                        ->pluck('id')
                        ->all();

                    $permissions = array_diff($permissions, $permissionsToRemove);
                }

                $role->syncPermissions($permissions);

                foreach ($item['simple_permission'] as $simplePermission) {
                    $role->givePermissionTo($simplePermission);
                }
            }
        }
    }
}
