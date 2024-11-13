<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Merchant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::factory(3)->create([]);
        $merchant = Merchant::factory(10)->create([]);

        $tenant = Tenant::updateOrCreate([
            "name" => "CloudMarket"
        ], [
            "domain" => "localhost",
            "database" => "cloudmarket"
        ]);

        $support_user = User::updateOrCreate([
            'email' => 'support@example.com',
        ], [
            'name' => 'Support User',
            'tenant_id' => $tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $company_admin_user = User::updateOrCreate([
            'email' => 'companyadmin@example.com',
        ], [
            'name' => 'Company Admin User',
            'tenant_id' => $tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_user = User::updateOrCreate([
            'email' => 'sales@example.com',
        ], [
            'name' => 'Sales Rep',
            'tenant_id' => $tenant->id,
            'password' => Hash::make('werwerwer'),
        ]);

        $order_manager_user = User::updateOrCreate([
            'email' => 'orders@example.com',
        ], [
            'name' => 'Order Manager',
            'tenant_id' => $tenant->id,
            'parent_id' => 3,
            'password' => Hash::make('werwerwer'),
        ]);

        $tenant->users()->attach([$support_user->id, $company_admin_user->id,  $sales_user->id, $order_manager_user->id]);

        $supp = User::where('email', 'support@example.com')->first();
        $supp->assignRole('Support Agent');

        $cAdmin = User::where('email', 'companyadmin@example.com')->first();
        $cAdmin->assignRole('Company Admin');

        $salesR = User::where('email', 'sales@example.com')->first();
        $salesR->assignRole('Sales Rep');

        $order_manager = User::where('email', 'orders@example.com')->first();
        $order_manager->assignRole('Order Manager');
    }
}
