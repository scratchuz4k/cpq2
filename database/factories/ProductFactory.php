<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

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

        $product_name = $brand_name . ' ' . fake()->word() . ' ' . fake()->buildingNumber();

        return [
            'tenant_id' => Tenant::where("name", "CloudMarket")->value('id'),
            'name' => $product_name,
            'code' => Str::slug($product_name),
        ];
    }
}
