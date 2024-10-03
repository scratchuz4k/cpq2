<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\SolutionType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class InitTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::updateOrCreate([
            "name" => "CloudMarket"
        ],[
            "domain" => "cloudmarket.com",
            "database" => "cloudmarket"
        ]);

        $super_user = User::updateOrCreate([
            'email' => 'super@example.com',
        ], [
            'name' => 'Super User',
            'password' => Hash::make('werwerwer'),
        ]);

        $support_user = User::updateOrCreate([
            'email' => 'support@example.com',
        ], [
            'name' => 'Support User',
            'password' => Hash::make('werwerwer'),
        ]);

        $company_admin_user = User::updateOrCreate([
            'email' => 'companyadmin@example.com',
        ], [
            'name' => 'Company Admin User',
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_user = User::updateOrCreate([
            'email' => 'sales@example.com',
        ], [
            'name' => 'Sales Rep',
            'password' => Hash::make('werwerwer'),
        ]);

        $tenant->users()->sync([$super_user->id, $support_user->id, $company_admin_user->id,  $sales_user->id]);

        SolutionType::updateOrCreate([
            "code" => 3
        ], [
            "name" => "Connectivity - Ethernet & Fibre",
        ]);
        SolutionType::updateOrCreate([
            "code" => 4
        ], [
            "name" => "Mobile",
        ]);
        SolutionType::updateOrCreate([
            "code" => 5
        ], [
            "name" => "Hardware",
        ]);
    }
}
