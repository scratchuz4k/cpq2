<?php

namespace Database\Seeders;

use App\Models\SolutionType;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SolutionTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        $solutions = SolutionType::whereIn('code', [3, 4])->get();

        $solutions->each(function ($solution) use ($tenants) {
            $solution->tenants()->sync($tenants);
        });
    }
}
