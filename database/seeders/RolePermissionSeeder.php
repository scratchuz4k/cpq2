<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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
                'tenant' => 'Tenant',
                'order' => 'Order',
                'order_item' => 'Order Item',
                'account' => 'Account',
                'contact' => 'Contact',
                'price' => 'Price',
                'category' => 'Category',
                'product' => 'Product',
                'supplier' => 'Supplier',
                'spa' => 'Spa',
                'user' => 'User',
                'token' => 'Token',
                'quote' => 'Quote',
                'quote_item' => 'Quote Item',
                'quote_approval' => 'Quote Approval',
                'solution_type' => 'Solution Type',
                'email_template' => 'Email Template',
                'email_history' => 'Email History',
                'api' => 'API',
                'price_plan' => 'Price Plan',
                'activity' => 'Activity',
                'export' => 'Export',
                'group' => 'Groups',
                'blacklist' => 'Blacklists',
                'import' => 'Import',
            ],
        ];

        $roles = [
            'Super Admin' => [
                'parent' => null,
                'permissions' => [],
                'simple_permission' => [],
            ],
            'Company Admin' => [
                'parent' => 'Super Admin',
                'permissions' => [
                    'quote', 'quote_item', 'order', 'order_item', 'account', 'contact',
                    'price', 'product', 'category', 'tenant', 'supplier',
                    'spa', 'user', 'solution_type', 'api', 'price_plan', 'activity', 'email_template', 'email_history',
                    'group', 'blacklist', 'import', 'quote_approval',
                ],
                'simple_permission' => ['viewAny export'],
            ],
            'Support Agent' => [
                'parent' => 'Company Admin',
                'permissions' => ['account', 'contact', 'token', 'quote', 'quote_item', 'order', 'order_item', 'activity', 'price_plan'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category'],
                'deny_permission' => ['export'],
            ],
            'Sales Manager' => [
                'parent' => 'Company Admin',
                'permissions' => ['quote', 'quote_item', 'account', 'contact', 'supplier', 'activity', 'price_plan', 'user', 'group', 'quote_approval'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category'],
            ],
            'Sales Rep' => [
                'parent' => 'Sales Manager',
                'permissions' => ['quote', 'quote_item', 'account', 'contact', 'supplier', 'activity', 'price_plan'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category', 'viewAny quote_approval', 'view quote_approval', 'create quote_approval'],
                'deny_permission' => ['export'],
            ],
            'Order Manager' => [
                'parent' => 'Company Admin',
                'permissions' => ['order', 'order_item', 'activity'],
                'simple_permission' => [
                    'viewAny product',
                    'view product',
                    'viewAny category',
                    'view category',
                    'viewAny account',
                    'view account',
                    'viewAny contact',
                    'view contact',
                    'viewAny supplier',
                    'view supplier',
                ],
                'deny_permission' => ['export'],
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
            $temp = null;

            if (! is_null($item['parent'])) {
                $temp = Role::where('name', $item['parent'])->firstOrFail();
            }

            $role = Role::updateOrCreate(['name' => $role], ['parent_id' => $temp?->id, 'guard_name' => 'web']);
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

        $sAdmin = User::where('email', env('SUPERUSER_EMAIL', 'super@example.com'))->first();
        $sAdmin->assignRole('Super Admin');
    }
}
