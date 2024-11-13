<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategoryProductsSeeder extends Seeder
{
    /**
     * Seed the application"s database.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                "code" => "basic",
                "name" => "Basic",
                "description" => "Basic / 5 GB / 100min",
                "price" => 10,
                "category" => "plan",
            ],
            [
                "code" => "premium",
                "name" => "Premium",
                "description" => "Premium / 20 GB / 500min",
                "price" => 20,
                "category" => "plan",
            ],
            [
                "code" => "traveller",
                "name" => "Traveller",
                "description" => "Traveller / Unlimited / 1000min",
                "price" => 30,
                "category" => "plan",
            ],
            [
                "code" => "500_uk_minute_bundle",
                "name" => "500 UK minute bundle",
                "description" => null,
                "price" => 0,
                "priceMonthly" => 10,
                "category" => "plan",
            ],
            [
                "code" => "share_bundle",
                "name" => "Share bundle",
                "description" => null,
                "price" => 0,
                "priceMonthly" => 20,
                "category" => "plan",
            ],
        ];

        foreach (Tenant::all() as $tenant) {
            $categories = [];
            $pricePlan = PricePlan::where("code", "monthly")->first();
            $categories["plan"]  = Category::updateOrCreate(["code" => "plan", "tenant_id" => $tenant->id], ["name" => "Plans"]);

            foreach ($products as $temp) {
                $product = Product::updateOrCreate([
                    "code" => $temp["code"],
                    "tenant_id" => $tenant->id
                ], [
                    "name" => $temp["name"],
                    "description" => $temp["description"],
                ]);

                $price = $product->prices()->updateOrCreate(
                    [
                        "code" => 1,
                        "tenant_id" => $tenant->id
                    ],
                    [
                        "tax_rate" => 20,
                        "net" => $temp["price"] ?? 0,
                        "price_plan_id" => $pricePlan->id
                    ]
                );

                $price->calculate();

                $product->categories()->attach($categories[$temp["category"]]);
            }
        }
    }
}
