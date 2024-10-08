<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DummySeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::factory(3)->create([]);
        $merchant = Merchant::factory(10)->create([]);
    }
}
