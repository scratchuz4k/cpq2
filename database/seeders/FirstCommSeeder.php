<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FirstCommSeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::findOrFail(9);
        $tenant->update([
            'name' => 'Firstcom',
            'domain' => 'firstcom.example.com',
            'database' => 'firstcom',
        ]);
    }
}
