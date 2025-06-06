<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Tenant::all() as $tenant) {
            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'its',
            ], [
                'name' => 'ITS',
            ]);

            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'pxc',
            ], [
                'name' => 'PXC',
            ]);

            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'gamma',
            ], [
                'name' => 'Gamma',
            ]);

            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'giacom',
            ], [
                'name' => 'Giacom',
            ]);

            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'vorboss',
            ], [
                'name' => 'Vorboss',
            ]);

            Supplier::updateOrCreate([
                'tenant_id' => $tenant->id,
                'reference' => 'ipriver',
            ], [
                'name' => 'IP River',
            ]);
        }
    }
}
