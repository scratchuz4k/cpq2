<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\InitTableSeeder;
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
            SolutionTenantSeeder::class,
        ]);

        if (config('app.env') === 'local') {
            $this->call([
                DummySeeder::class
            ]);
        }
    }
}
