<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
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
                'tenant' => 'Tenant',
                'order' => 'Order',
                'order_item' => 'Order Item',
                'merchant' => 'Merchant',
                'account' => 'Account',
                'contact' => 'Contact',
                'order_items' => 'Order Items',
                'price' => 'Price',
                'product' => 'Product',
                'region' => 'Region',
                'brand' => 'Brand',
                'supplier' => 'Supplier',
                'spa' => 'Spa',
                'user' => 'User',
                'token' => 'Token'
            ],
        ];

        $roles = [
            'Super Admin' => [
                'parent' => null,
                'permissions' => [],
                'simple_permission' => []
            ],
            'Support Agent' => [
                'parent' => 'Super Admin',
                'permissions' => ['merchant', 'account', 'contact', 'token', 'order', 'order_items'],
                'simple_permission' => ['viewAny product', 'view product']
            ],
            'Company Admin' => [
                'parent' => 'Super Admin',
                'permissions' => ['order', 'order_items', 'merchant', 'account', 'contact',  'price', 'product', 'region', 'tenant', 'brand', 'supplier', 'spa', 'user'],
                'simple_permission' => []
            ],
            'Sales Rep' => [
                'parent' => 'Company Admin',
                'permissions' => ['order', 'order_items', 'account', 'contact', 'brand', 'supplier'],
                'simple_permission' => ['viewAny product', 'view product']
            ]
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
            $temp = null;

            if (!is_null($item['parent'])) {
                $temp = Role::where('name', $item['parent'])->firstOrFail();
            }

            $role = Role::updateOrCreate(['name' => $role], ['parent_id' => $temp?->id, 'guard_name' => 'web']);
            foreach ($item['permissions'] as $group) {
                if (array_key_exists($group, $insertPermissions->toArray())) {
                    $permissions = array_merge($permissions, $insertPermissions->toArray()[$group]);
                }
            }
            $role->syncPermissions($permissions);

            foreach ($item['simple_permission'] as $simplePermission) {
                $role->givePermissionTo($simplePermission);
            }
        }

        $sAdmin = User::where('email', 'super@example.com')->first();
        $sAdmin->assignRole('Super Admin');
    }
}
