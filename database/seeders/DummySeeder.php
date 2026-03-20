<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    public function run()
    {
        $main_tenant = Tenant::updateOrCreate([
            'name' => 'CloudMarket',
        ], [
            'domain' => 'example.com',
            'database' => 'cloudmarket',
        ]);
        $dummy_tenants = Tenant::factory(2)->create([]);

        $tenants = $dummy_tenants->prepend($main_tenant);

        foreach ($tenants as $tenant) {
            Account::factory(10)->create([
                'tenant_id' => $tenant->id,
            ]);
        }

        $support_user = User::updateOrCreate([
            'email' => 'support@example.com',
        ], [
            'name' => 'Support User',
            'tenant_id' => $main_tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $company_admin_user = User::updateOrCreate([
            'email' => 'companyadmin@example.com',
        ], [
            'name' => 'Company Admin User',
            'tenant_id' => $main_tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_manager_user = User::updateOrCreate([
            'email' => 'salesmanager@example.com',
        ], [
            'name' => 'Sales Manager',
            'tenant_id' => $main_tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_user = User::updateOrCreate([
            'email' => 'sales@example.com',
        ], [
            'name' => 'Sales Rep',
            'tenant_id' => $main_tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $main_tenant->users()->sync([
            $support_user->id,
            $company_admin_user->id,
            $sales_manager_user->id,
            $sales_user->id,
        ]);

        $super_user = User::where('email', config('cpq.superuser.email'))->first();
        $super_user->tenants()->sync($tenants->pluck('id'));

        $system_user = User::where('email', 'system@cpq.cloudmarket.com')->first();
        $system_user->tenants()->sync($tenants->pluck('id'));
    }
}
