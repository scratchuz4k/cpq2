<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopBlocksSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Read the SQL file
        $sql = file_get_contents(storage_path('mocks/dump-shopblocks_db.sql'));

        // Use the connection() method to select your Laravel connection
        DB::connection('mysql_shopblocks')->unprepared($sql);
    }
}
