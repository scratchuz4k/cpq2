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
                'order' => 'Order',
                'merchant' => 'Merchant',
                'account' => 'Account',
                'order_items' => 'Order Items',
                'price' => 'Price',
                'product' => 'Product',
                'region' => 'Region',
            ],
        ];

        $roles = [
            'Super Admin' => [],
            'Admin' => ['Admin', 'order', 'order_items', 'merchant', 'account',  'price', 'product', 'region'],
            'Support Agent' => ['merchant'],
            'Company Admin' => ['order', 'order_items', 'merchant', 'account',  'price', 'product', 'region'],
            'Sales Rep' => ['order', 'order_items', 'account']
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

            foreach ($values as $valueKey => $value) {
                $ids->push(...collect($permissions)
                    ->map(function ($name, $index) use ($group, $valueKey, $value) {
                        return Permission::updateOrCreate(['name' => "$index $valueKey", 'guard_name' => 'web'], ['group' => $group, 'title' => "$name $value"]);
                    })
                    ->pluck('id')
                    ->toArray());
                $insertPermissions[$valueKey] = $ids;
            }
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

        $sAdmin = User::where('email', 'super@example.com',)->first();
        $sAdmin->assignRole('Super Admin');

        $supp = User::where('email', 'support@example.com',)->first();
        $supp->assignRole('Support Agent');

        $cAdmin = User::where('email', 'companyadmin@example.com',)->first();
        $cAdmin->assignRole('Company Admin');

        $salesR = User::where('email', 'sales@example.com',)->first();
        $salesR->assignRole('Sales Rep');
    }
}
