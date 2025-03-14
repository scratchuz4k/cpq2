<?php

namespace Database\Seeders;

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
        ]);

        if (config('app.env') === 'local') {
            $this->call([
                DummySeeder::class,
                CategoryProductsSeeder::class,
                ApiConfigurationSeeder::class,
            ]);
        }

        $this->call([
            SolutionTenantSeeder::class,
        ]);
    }
}
