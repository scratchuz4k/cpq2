<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ShopBlocks\Brand;
use App\Models\ShopBlocks\Category;
use App\Models\ShopBlocks\Model;
use App\Models\ShopBlocks\Product;
use App\Models\ShopBlocks\ProductFeature;
use Illuminate\Database\Seeder;
use MongoDB\BSON\ObjectId;

class ShopBlocksSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $mock = storage_path('mocks/shopblocks.products.json');
        if (is_file($mock)) {
            $items = json_decode(file_get_contents($mock), true);
            $items = array_map(function ($item) {
                $item['id'] = new ObjectId($item['id']);

                return $item;
            }, $items);
            Product::truncate();
            Product::insert($items);
        }
        $mock = storage_path('mocks/shopblocks.product_features.json');
        if (is_file($mock)) {
            $items = json_decode(file_get_contents($mock), true);
            $items = array_map(function ($item) {
                $item['id'] = new ObjectId($item['id']);

                return $item;
            }, $items);
            ProductFeature::truncate();
            ProductFeature::insert($items);
        }
        $mock = storage_path('mocks/shopblocks.brands.json');
        if (is_file($mock)) {
            $items = json_decode(file_get_contents($mock), true);
            $items = array_map(function ($item) {
                $item['id'] = new ObjectId($item['id']);

                return $item;
            }, $items);
            Brand::truncate();
            Brand::insert($items);
        }
        $mock = storage_path('mocks/shopblocks.models.json');
        if (is_file($mock)) {
            $items = json_decode(file_get_contents($mock), true);
            $items = array_map(function ($item) {
                $item['id'] = new ObjectId($item['id']);

                return $item;
            }, $items);
            Model::truncate();
            Model::insert($items);
        }
        $mock = storage_path('mocks/shopblocks.categories.json');
        if (is_file($mock)) {
            $items = json_decode(file_get_contents($mock), true);
            Category::truncate();
            Category::insert($items);
        }
    }
}
