<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TTLIndexSeeder extends Seeder
{
    public function run()
    {
        DB::connection('mongodb_shopblocks')->table('products')
            ->raw(function ($collection) {
                $collection->createIndex(
                    ['expire_at' => 60],
                    ['expireAfterSeconds' => 0]
                );
            });
        DB::connection('mongodb_shopblocks')->table('product_features')
            ->raw(function ($collection) {
                $collection->createIndex(
                    ['expire_at' => 60],
                    ['expireAfterSeconds' => 0]
                );
            });
        DB::connection('mongodb_cloudmarket')->table('products')
            ->raw(function ($collection) {
                $collection->createIndex(
                    ['expire_at' => 60],
                    ['expireAfterSeconds' => 0]
                );
            });
    }
}
