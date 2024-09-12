<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        $tenant = Tenant::factory()->create([]);

        $super_user = User::factory()->create([
            'name' => 'Super User',
            'email' => 'super@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $support_user = User::factory()->create([
            'name' => 'Support User',
            'email' => 'support@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $company_admin_user = User::factory()->create([
            'name' => 'Company Admin User',
            'email' => 'companyadmin@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $sales_user = User::factory()->create([
            'name' => 'Sales Rep',
            'email' => 'sales@example.com',
            'password' => Hash::make('werwerwer'),
        ]);

        $tenant->users()->sync([$super_user->id, $support_user->id, $company_admin_user->id,  $sales_user->id]);
    }
}
