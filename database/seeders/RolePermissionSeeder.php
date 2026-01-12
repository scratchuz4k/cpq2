<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public static $simplePermissions = [
        'Website' => [
            'viewOwn quote' => 'View Own Quote',
        ],
        'Managament' => [
            'viewAny report' => 'View All Report',
        ],
    ];

    public static $permissions = [
        'viewAny' => 'View All',
        'view' => 'View Details',
        'create' => 'Create',
        'update' => 'Edit',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'forceDelete' => 'Force Delete',
        'export' => 'Export',
    ];

    // Reset cached roles and permissions

    public static $permissionsByGroup = [
        'Website' => [
            'quote' => 'Quote',
            'quote_item' => 'Quote Item',
            'quote_approval' => 'Quote Approval',
            'price' => 'Price',
            'category' => 'Category',
            'product' => 'Product',
            'price_plan' => 'Price Plan',
            'blacklist' => 'Blacklists',
            'step' => 'Step',
            'notification' => 'Notification',
        ],
        'Commercial' => [
            'account' => 'Account',
            'contact' => 'Contact',
            'spa' => 'Spa',
            'supplier' => 'Supplier',
        ],
        'Settings' => [
            'email_template' => 'Email Template',
            'email_history' => 'Email History',
            'solution_type' => 'Solution Type',
            'activity' => 'Activity',
        ],
        'Managament' => [
            'group' => 'Groups',
            'user' => 'User',
            'tenant' => 'Tenant',
            'token' => 'Token',
            'api' => 'API',
            'permission' => 'Permission',
            'role' => 'Role',
            'import' => 'Import',
        ],
    ];

    public static $roles =
        [
            'Company Admin' => [
                'parent' => null,
                'tenant' => 1,
                'permissions' => [
                    'quote', 'quote_item', 'account', 'contact',
                    'price', 'product', 'category', 'tenant', 'supplier',
                    'spa', 'user', 'solution_type', 'api', 'price_plan', 'activity', 'email_template', 'email_history',
                    'group', 'blacklist', 'role', 'permission', 'step', 'import', 'quote_approval', 'notification',
                ],
                'simple_permission' => ['viewAny report'],
            ],
            'Support Agent' => [
                'parent' => 'Company Admin',
                'tenant' => 1,
                'permissions' => ['account', 'contact', 'token', 'quote', 'quote_item', 'activity', 'price_plan', 'notification'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category', 'viewAny step', 'view step'],
                'deny_permission' => ['export'],
            ],
            'Sales Manager' => [
                'parent' => 'Company Admin',
                'tenant' => 1,
                'permissions' => ['quote', 'quote_item', 'account', 'contact', 'supplier', 'activity', 'price_plan', 'user', 'group', 'role', 'permission', 'quote_approval', 'notification'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category', 'viewAny step', 'view step'],
            ],
            'Sales Rep' => [
                'parent' => 'Sales Manager',
                'tenant' => 1,
                'permissions' => ['quote', 'quote_item', 'account', 'contact', 'supplier', 'activity', 'price_plan', 'notification'],
                'simple_permission' => ['viewAny product', 'view product', 'viewAny category', 'view category', 'viewAny step', 'view step', 'viewAny quote_approval', 'view quote_approval', 'create quote_approval'],
                'deny_permission' => ['export'],
            ],
        ];

    public function run()
    {
        $tenant = Tenant::first();
        $tenant->makeCurrent();

        $insertPermissions = collect();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = in_array(config('app.env'), ['local', 'testing']) ? self::$roles : [];

        foreach (self::$simplePermissions as $key => $pe) {
            $ids = collect($pe)
                ->map(function ($name, $index) use ($key) {
                    return Permission::updateOrCreate(['name' => "$index", 'guard_name' => 'web'], ['group' => $key, 'title' => "$name"]);
                })
                ->pluck('id')
                ->toArray();

            $insertPermissions[$key] = $ids;
        }

        foreach (self::$permissionsByGroup as $group => $values) {
            foreach ($values as $valueKey => $value) {
                $ids = collect();
                $ids->push(...collect(self::$permissions)
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
                $temp = Role::withoutGlobalScopes()->where('name', $item['parent'])->firstOrFail();
            }

            $role = Role::updateOrCreate(['name' => $role], ['guard_name' => 'web', 'parent_id' => $temp?->id, 'tenant_id' => $item['tenant']]);
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

        $this->removeOldPermissions();
    }

    public function removeOldPermissions(): void
    {
        $validPermissions = array_reduce(
            self::$simplePermissions,
            fn ($carry, $item) => array_merge($carry, array_keys($item)),
            []
        );

        foreach (self::$permissionsByGroup as $permissionGroup) {
            foreach (array_keys($permissionGroup) as $groupKey) {
                foreach (array_keys(self::$permissions) as $action) {
                    $validPermissions[] = "{$action} {$groupKey}";
                }
            }
        }

        $deletedCount = Permission::query()
            ->whereNotIn('name', $validPermissions)
            ->delete();
    }
}
