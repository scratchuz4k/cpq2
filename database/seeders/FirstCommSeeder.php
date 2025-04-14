<?php

namespace Database\Seeders;

use App\Enums\PricePlanEnum;
use App\Models\Api;
use App\Models\Category;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\SolutionType;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FirstCommSeeder extends Seeder
{
    public function run()
    {
        //Tenant and Users
        $tenant = Tenant::updateOrCreate([
            'name' => 'FirstComm',
        ], [
            'domain' => 'firstcomm.example.com',
            'database' => 'firstcomm',
        ]);

        //Permissions
        /*
        $this->call([
            RolePermissionSeeder::class,
        ]);
        */
        //API Configurations
        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'its',
        ], [
            'name' => 'ITS',
            'auth_type' => 'token',
            'is_active' => true,
        ]);

        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'virtual1',
        ], [
            'name' => 'Virtual1',
            'auth_type' => 'basic',
            'is_active' => true,
        ]);

        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'gamma',
        ], [
            'name' => 'Gamma',
            'auth_type' => 'basic_client',
            'is_active' => true,
        ]);

        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'giacom',
        ], [
            'name' => 'Giacom',
            'auth_type' => 'basic',
            'is_active' => true,
        ]);

        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'vorboss',
        ], [
            'name' => 'Vorboss',
            'auth_type' => 'token',
            'is_active' => true,
        ]);

        Api::updateOrCreate([
            'tenant_id' => $tenant->id,
            'code' => 'ipriver',
        ], [
            'name' => 'IP River',
            'auth_type' => 'basic',
            'is_active' => true,
        ]);

        //Solution Types
        $solutions = SolutionType::whereIn('code', [3, 4])->get();

        $solutions->each(function ($solution) use ($tenant) {
            $solution->tenants()->attach($tenant->id);
        });

        //Categories and Products
        $products = [
            [
                'code' => 'basic',
                'name' => 'Basic',
                'description' => 'Basic / 5 GB / 100min',
                'price' => 10,
                'category' => 'plan',
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'description' => 'Premium / 20 GB / 500min',
                'price' => 20,
                'category' => 'plan',
            ],
            [
                'code' => 'traveller',
                'name' => 'Traveller',
                'description' => 'Traveller / Unlimited / 1000min',
                'price' => 30,
                'category' => 'plan',
            ],
            [
                'code' => '500_uk_minute_bundle',
                'name' => '500 UK minute bundle',
                'description' => null,
                'price' => 10,
                'category' => 'plan',
            ],
            [
                'code' => 'share_bundle',
                'name' => 'Share bundle',
                'description' => null,
                'price' => 20,
                'category' => 'plan',
            ],
        ];

        $categories = [];
        $pricePlan = PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first();
        $categories['plan'] = Category::updateOrCreate(['code' => 'plan', 'tenant_id' => $tenant->id], ['name' => 'Plans']);

        foreach ($products as $temp) {
            $product = Product::updateOrCreate([
                'code' => $temp['code'],
                'tenant_id' => $tenant->id,
            ], [
                'name' => $temp['name'],
                'description' => $temp['description'],
                'price_plan_id' => $pricePlan->id,
            ]);

            $price = $product->prices()->updateOrCreate(
                [
                    'code' => 1,
                    'tenant_id' => $tenant->id,
                ],
                [
                    'tax_rate' => 20,
                    'net' => $temp['price'] ?? 0,
                ]
            );

            $price->calculate();

            $product->categories()->attach($categories[$temp['category']]);
        }
    }
}
