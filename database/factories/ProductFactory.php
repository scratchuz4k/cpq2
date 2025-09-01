<?php

namespace Database\Factories;

use App\Enums\ProductStatusesEnum;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $brand_name = Str::ucfirst(Str::words(preg_replace('/[^A-Za-z]/', ' ', strtolower(fake()->company)), 1, ''));

        $product_name = $brand_name.' '.fake()->word().' '.fake()->buildingNumber();

        return [
            'tenant_id' => Tenant::where('name', 'CloudMarket')->value('id'),
            'sku' => Str::slug($product_name),
            'name' => $product_name,
            'description' => fake()->optional()->text(),
            'supplier_id' => fake()->optional()->numberBetween(1, 99),
            'category_id' => fake()->numberBetween(1, 99),
            'status' => ProductStatusesEnum::randomValue(),
            'expires_at' => optional(fake()->optional(0.7)->dateTimeBetween('now', '+30 days'))->format('Y-m-d'),
            'price_plan_id' => random_int(1, 2),
        ];
    }
}
