<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Spa;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $permission = Permission::create(['name' => 'edit merchants']);
        $permission = Permission::create(['name' => 'edit users']);
        $permission = Permission::create(['name' => 'edit api tokens']);
        $permission = Permission::create(['name' => 'edit accounts']);
        $permission = Permission::create(['name' => 'edit brands']);
        $permission = Permission::create(['name' => 'edit orders']);
        $permission = Permission::create(['name' => 'edit order_items']);
        $permission = Permission::create(['name' => 'edit prices']);
        $permission = Permission::create(['name' => 'edit products']);
        $permission = Permission::create(['name' => 'edit regions']);

        // access to everything, including the creation of new merchants (tenants)
        $role = Role::create(['name' => 'super user']);

        // overall admin of all tenant data
        $role = Role::create(['name' => 'support agent']);
        $role->givePermissionTo('edit merchants');
        $role->givePermissionTo('edit api tokens');

        // admin of a single merchant (tenant) operations/users
        $role = Role::create(['name' => 'company admin']);
        $role->givePermissionTo('edit accounts');
        $role->givePermissionTo('edit brands');
        $role->givePermissionTo('edit orders');
        $role->givePermissionTo('edit order_items');
        $role->givePermissionTo('edit prices');
        $role->givePermissionTo('edit products');
        $role->givePermissionTo('edit regions');
        $role->givePermissionTo('edit users');

        // lowest-level role - strictly carry out sales/orders operations on own accounts
        $role = Role::create(['name' => 'sales rep']);
        $role->givePermissionTo('edit orders');
        $role->givePermissionTo('edit order_items');
        $role->givePermissionTo('edit accounts');

        $super_user = \App\Models\User::factory()->create([
            'name' => 'Super User',
            'email' => 'super@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $super_user->assignRole('super user');

        $support_user = \App\Models\User::factory()->create([
            'name' => 'Support User',
            'email' => 'support@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $support_user->assignRole('support agent');
        $support_user->assignRole('sales rep');
        $support_user->assignRole('company admin');

        $company_admin_user = \App\Models\User::factory()->create([
            'name' => 'Company Admin User',
            'email' => 'companyadmin@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $company_admin_user->assignRole('company admin');
        $company_admin_user->assignRole('sales rep');

        $sales_user = \App\Models\User::factory()->create([
            'name' => 'Sales Rep',
            'email' => 'sales@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_user->assignRole('sales rep');

        \App\Models\Tenant::factory(3)->create();
        \App\Models\Supplier::factory(100)->create();
        \App\Models\Merchant::factory(10)->create();
        \App\Models\Region::factory(10)->create();
        Account::withoutEvents(function() {
            Account::factory(100)
                ->hasContacts(5)
                ->create();
        });
        \App\Models\Brand::factory(10)->create();
        \App\Models\Product::factory(10)->create();
        \App\Models\Order::factory(100)->create();
        \App\Models\OrderItem::factory(100)->create();
        \App\Models\BrandMerchant::factory(1)->create();
        \App\Models\MerchantSupplier::factory(1)->create();
        \App\Models\TenantUser::factory(1)->create();
        \App\Models\Spa::factory(100)->create();
        \App\Models\Price::factory(10)->create();

        for ($t = 1; $t <=3; $t++) {
            Supplier::create([
                'tenant_id' => $t,
                'name' => 'ITS',
                'reference' => 'its',
            ]);
            Supplier::create([
                'tenant_id' => $t,
                'name' => 'Giacom',
                'reference' => 'giacom',
            ]);
            Supplier::create([
                'tenant_id' => $t,
                'name' => 'Gamma',
                'reference' => 'gamma',
            ]);
            Supplier::create([
                'tenant_id' => $t,
                'name' => 'Virtual1',
                'reference' => 'virtual1',
            ]);
            Spa::create([
                'tenant_id' => $t,
                'supplier_id' => 11,
                'data' => '{"multiply": "1.5"}',
            ]);
        }
    }
}
