<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;
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
            "tenant_id" => 1,
            "code" => 1,
            "tax_rate" => $tax_rate,
            "net" => $net,
            "tax" => $tax,
            "gross" => $gross,
            "price_plan_id" => 1,
        ];
    }
}
