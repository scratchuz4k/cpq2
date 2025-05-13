<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TTLIndexSeeder extends Seeder
{
    public function run()
    {
        DB::connection('mongodb')->table('shop_blocks_products')
            ->raw(function ($collection) {
                $collection->createIndex(
                    ['expire_at' => 5],
                    ['expireAfterSeconds' => 0]
                );
            });
    }
}
