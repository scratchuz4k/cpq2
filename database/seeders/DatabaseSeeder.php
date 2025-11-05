<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            InitTableSeeder::class,
            RolePermissionSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        if (config('app.env') === 'local') {
            $this->call([
                DummySeeder::class,
                ApiConfigurationSeeder::class,
                SolutionTenantSeeder::class,
                StepProductsSeeder::class,
                ShopBlocksSeeder::class,
            ]);
        }

    }
}
