<?php

namespace Database\Seeders;

use App\Models\Api;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ApiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Tenant::all() as $tenant) {
            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'its',
            ], [
                'name' => 'ITS',
                'auth_type' => 'token',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'virtual1',
            ], [
                'name' => 'Virtual1',
                'auth_type' => 'basic',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'gamma',
            ], [
                'name' => 'Gamma',
                'auth_type' => 'basic_client',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'giacom',
            ], [
                'name' => 'Giacom',
                'auth_type' => 'basic',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'vorboss',
            ], [
                'name' => 'Vorboss',
                'auth_type' => 'token',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'ipriver',
            ], [
                'name' => 'IP River',
                'auth_type' => 'basic',
                'is_active' => true,
            ]);

            Api::updateOrCreate([
                'tenant_id' => $tenant->id,
                'code' => 'VMB',
            ], [
                'name' => 'VMB',
                'auth_type' => 'vmo2_client',
                'is_active' => true,
            ]);
        }
    }
}
