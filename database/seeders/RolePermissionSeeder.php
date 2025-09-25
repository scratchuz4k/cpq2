<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $insertPermissions = collect();

        $simplePermissions = [
            'Admin' => [
                'access admin' => 'Administration Access',
            ],
            'Website' => [
                'viewOwn quote' => 'ListOwn Quote',
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
            'export' => 'Export',
        ];

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsByGroup = [
            'Website' => [
                'order' => 'Order',
                'order_item' => 'Order Item',
                'quote' => 'Quote',
                'quote_item' => 'Quote Item',
                'price' => 'Price',
                'category' => 'Category',
                'product' => 'Product',
                'brand' => 'Brand',
                'user' => 'User',
                'price_plan' => 'Price Plan',
                'blacklist' => 'Blacklists',
            ],
            'Commercial' => [
                'merchant' => 'Merchant',
                'account' => 'Account',
                'contact' => 'Contact',
                'region' => 'Region',
                'spa' => 'Spa',
                'supplier' => 'Supplier',
            ],
            'Managament' => [
                'email_template' => 'Email Template',
                'email_history' => 'Email History',
                'solution_type' => 'Solution Type',
                'export' => 'Export',
                'tenant' => 'Tenant',
                'token' => 'Token',
                'group' => 'Groups',
                'activity' => 'Activity',
                'api' => 'API',
                'permission' => 'Permission',
                'role' => 'Role',
            ],
        ];

        $roles = [
            'Super Admin' => [
                'permissions' => [],
                'simple_permission' => [],
            ],
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
            foreach ($values as $valueKey => $value) {
                $ids = collect();
                $ids->push(...collect($permissions)
                    ->map(function ($name, $index) use ($group, $valueKey, $value) {
                        return Permission::updateOrCreate(['name' => "$index $valueKey", 'guard_name' => 'web'], ['group' => $group, 'title' => "$name $value"]);
                    })
                    ->pluck('id')
                    ->toArray());
                $insertPermissions[$valueKey] = $ids;
            }
        }

        foreach ($roles as $role => $item) {
            $permissions = [];

            $role = Role::updateOrCreate(['name' => $role], ['guard_name' => 'web']);
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
