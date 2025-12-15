<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\CloudAuditStatusEnum;
use App\Models\Cloudaudit\Organization;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CloudAuditSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Organization::query()->updateOrCreate([
            'tenant_id' => Tenant::first()->id,
            'code' => '1-sergio-cardana',
        ], [
            'name' => 'Sergio Cardana',
            'email' => 'sergio.cardana@evolveportugal.com',
            'status' => CloudAuditStatusEnum::GRANTED,
        ]);
    }
}
