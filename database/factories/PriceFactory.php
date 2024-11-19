<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $net = $this->faker->randomFloat(2, 1, 1000);
        $tax_rate = $this->faker->randomFloat(2, 0.01, 0.50);
        $tax = round($net * $tax_rate, 2);
        $gross = round($net + $tax, 2);
        return [
            'tenant_id' => Tenant::where("name", "CloudMarket")->value('id'),
            'spa_id' => 1,
            'product_id' => 1,
            'tax_rate' => $tax_rate,
            'tenant_id' => $this->faker->numberBetween(1, 3),
            'spa_id' => 1,
            'product_id' => 1,
            'tax_rate' => $tax_rate * 100, // Store as a percentage
            'net' => $net,
            'tax' => $tax,
            'gross' => $gross,
        ];
    }
}
