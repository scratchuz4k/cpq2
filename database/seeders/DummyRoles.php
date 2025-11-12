<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyRoles extends Seeder
{
    public function run()
    {
        $tenant = Tenant::first();
        $tenant->makeCurrent();

        $supp = User::where('email', 'support@example.com')->first();
        $supp->roles()->attach(Role::where('name', 'Support Agent')->first()->id, ['tenant_id' => $tenant->id]);

        $cAdmin = User::where('email', 'companyadmin@example.com')->first();
        $cAdmin->roles()->attach(Role::where('name', 'Company Admin')->first()->id, ['tenant_id' => $tenant->id]);

        $salesR = User::where('email', 'sales@example.com')->first();
        $salesR->roles()->attach(Role::where('name', 'Sales Rep')->first()->id, ['tenant_id' => $tenant->id]);

        $order_manager = User::where('email', 'orders@example.com')->first();
        $order_manager->roles()->attach(Role::where('name', 'Order Manager')->first()->id, ['tenant_id' => $tenant->id]);

        $sales_manager = User::where('email', 'salesmanager@example.com')->first();
        $sales_manager->roles()->attach(Role::where('name', 'Sales Manager')->first()->id, ['tenant_id' => $tenant->id]);

    }
}
