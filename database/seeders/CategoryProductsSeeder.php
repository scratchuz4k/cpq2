<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
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
                "price" => 0,
                "priceMonthly" => 10,
                "category" => "plan",
            ],
            [
                "code" => "premium",
                "name" => "Premium",
                "description" => "Premium / 20 GB / 500min",
                "price" => 0,
                "priceMonthly" => 20,
                "category" => "plan",
            ],
            [
                "code" => "traveller",
                "name" => "Traveller",
                "description" => "Traveller / Unlimited / 1000min",
                "price" => 0,
                "priceMonthly" => 30,
                "category" => "plan",
            ],
            [
                "code" => "500_uk_minute_bundle",
                "name" => "500 UK minute bundle",
                "description" => null,
                "price" => 0,
                "priceMonthly" => 10,
                "category" => "bolt_on",
            ],
            [
                "code" => "share_bundle",
                "name" => "Share bundle",
                "description" => null,
                "price" => 0,
                "priceMonthly" => 20,
                "category" => "bolt_on",
            ],
        ];

        foreach (Tenant::all() as $tenant) {
            $categories = [];
            $categories["plan"]  = Category::updateOrCreate(["code" => "plan", "tenant_id" => $tenant->id], ["name" => "Plans"]);
            $categories["bolt_on"]  = Category::updateOrCreate(["code" => "bolt_on", "tenant_id" => $tenant->id], ["name" => "Bolt On"]);

            foreach ($products as $temp) {
                $product = Product::updateOrCreate([
                    "code" => $temp["code"],
                    "tenant_id" => $tenant->id
                ], [
                    "name" => $temp["name"],
                    "description" => $temp["description"],
                ]);

                $price = $product->prices()->updateOrCreate(
                    ["code" => 1, "tenant_id" => $tenant->id],
                    [
                        "tax_rate" => 20,
                        "net" => $temp["price"] ?? 0,
                        "net_monthly" => $temp["priceMonthly"] ?? 0
                    ]
                );

                $price->calculate();

                $product->categories()->attach($categories[$temp["category"]]);
            }
        }
    }
}
